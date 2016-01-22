<?php
namespace Rejoiner\Acr\Controller\Addbysku;


use \Magento\Framework\App\Action\Context;
use \Magento\Checkout\Model\CartFactory;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Checkout\Model\Session;
use \Rejoiner\Acr\Helper\Data;
use \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Model\ProductFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    const XML_PATH_REJOINER_DEBUG_ENABLED   = 'checkout/rejoiner_acr/debug_enabled';

    protected $_checkoutSession;
    protected $_rejoinerHelper;
    protected $_messageManager;
    protected $_cartModel;
    protected $_stockItemFactory;
    protected $_stockItem;

    protected $_storeManagerInterface;
    protected $_product;

    /**
     * @param Context $context
     * @param CartFactory $cartModel
     * @param ObjectManagerInterface $objectInterface
     * @param Session $checkoutSession
     * @param Data $rejoinerHelper
     * @param ItemFactory $stockItem
     * @param StockItemInterfaceFactory $stockItemFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param ProductFactory $product
     */
    public function __construct(
        Context $context,
        CartFactory $cartModel,
        ObjectManagerInterface $objectInterface,
        Session $checkoutSession,
        Data $rejoinerHelper,
        ItemFactory $stockItem,
        StockItemInterfaceFactory $stockItemFactory,
        StoreManagerInterface $storeManagerInterface,
        ProductFactory $product
    )
    {
        $this->_checkoutSession       = $checkoutSession;
        $this->_objectInterface       = $objectInterface;
        $this->_rejoinerHelper        = $rejoinerHelper;
        $this->_stockItemFactory      = $stockItemFactory;
        $this->_cartModel             = $cartModel;
        $this->_stockItem             = $stockItem;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_product               = $product;
        $this->_messageManager        = $context->getMessageManager();

        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $cart = $this->_cartModel->create();
        $successMessage = '';
        $storeId = $this->_storeManagerInterface->getStore()->getId();
        foreach ($params as $key => $product) {
            if ($product && is_array($product)) {
                $productModel = $this->_product->create();
                $productBySKU = $productModel->loadByAttribute('sku', $product['sku']);
                if (!$productBySKU->getId()) {
                    continue;
                }
                $productId = $productBySKU->getId();
                if ($productId) {
                    $stockItem = $this->_stockItemFactory->create();
                    /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $stockItemResource */
                    $stockItemResource = $this->_stockItem->create();
                    $stockItemResource->loadByProductId($stockItem, $productId, $storeId);
                    $qty = $stockItem->getQty();
                    try {
                        if(!$cart->getQuote()->hasProductId($productId) && is_numeric($product['qty']) && $qty > $product['qty']) {
                            $cart->addProduct($productBySKU, (int)$product['qty']);
                            $successMessage .= __('%1 was added to your shopping cart.'.'</br>', $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($productBySKU->getName()));
                        }
                        unset($params[$key]);
                    } catch (\Exception $e) {
                        if($this->_rejoinerHelper->getStoreConfig(self::XML_PATH_REJOINER_DEBUG_ENABLED)) {
                            $this->_rejoinerHelper->log($e->getMessage());
                        }
                    }
                }
            }
        }
        if (isset($params['coupon_code'])) {
            $cart->getQuote()->setCouponCode($params['coupon_code'])->collectTotals();
        }
        try {
            $cart->getQuote()->save();
            $cart->save();
        }  catch (\Exception $e) {
            if($this->_rejoinerHelper->getStoreConfig(self::XML_PATH_REJOINER_DEBUG_ENABLED)) {
                $this->_rejoinerHelper->log($e->getMessage());
            }
        }
        $this->_checkoutSession->setCartWasUpdated(true);

        if ($successMessage) {
            $this->_messageManager->addSuccess($successMessage);
        }
        $url = $this->_objectManager->get('\Magento\Framework\UrlInterface')->getUrl('checkout/cart/', ['updateCart' => true]);
        $this->getResponse()->setRedirect($url);
    }

}