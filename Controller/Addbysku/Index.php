<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Controller\Addbysku;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Rejoiner\Acr\Helper\Data;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;

class Index extends Action
{
    const XML_PATH_REJOINER_DEBUG_ENABLED   = 'checkout/rejoiner_acr/debug_enabled';

    /**
     * @var Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var Data $rejoinerHelper
     */
    protected $rejoinerHelper;

    /**
     * @var Cart $cartModel
     */
    protected $cartModel;

    /**
     * @var StockItemInterfaceFactory $stockItemFactory
     */
    protected $stockItemFactory;

    /**
     * @var ItemFactory $itemFactory
     */
    protected $itemFactory;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var ProductFactory $productFactory
     */
    protected $productFactory;

    /** @var Escaper $escaper */
    protected $escaper;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductRepositoryInterface $productRepositoryInterface
     */
    protected $productRepositoryInterface;


    /**
     * Index constructor.
     * @param Context $context
     * @param Cart $cartModel
     * @param Session $checkoutSession
     * @param Data $rejoinerHelper
     * @param ItemFactory $itemFactory
     * @param StockItemInterfaceFactory $stockItemFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        Cart $cartModel,
        Session $checkoutSession,
        Data $rejoinerHelper,
        ItemFactory $itemFactory,
        StockItemInterfaceFactory $stockItemFactory,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        Escaper $escaper,
        ProductRepositoryInterface $productRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->checkoutSession            = $checkoutSession;
        $this->rejoinerHelper             = $rejoinerHelper;
        $this->stockItemFactory           = $stockItemFactory;
        $this->cartModel                  = $cartModel;
        $this->itemFactory                = $itemFactory;
        $this->storeManager               = $storeManager;
        $this->productFactory             = $productFactory;
        $this->escaper                    = $escaper;
        $this->searchCriteriaBuilder      = $searchCriteriaBuilder;
        $this->productRepositoryInterface = $productRepositoryInterface;
        parent::__construct($context);
    }

    /**
     *  Adds products to shopping cart by product sku
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $cart = $this->cartModel;
        $successMessages = [];
        $storeId = $this->storeManager->getStore()->getId();
        $productSkuArray = array();
        foreach ($params as $key => $product) {
            if (is_array($product) && isset($product['sku'])) {
                $productSkuArray[$product['sku']] = $product['qty'];
            }
        }
        $filter = $this->searchCriteriaBuilder->addFilter('sku', array_keys($productSkuArray), 'in')->create();
        $searchResult = $this->productRepositoryInterface->getList($filter);
        foreach ($searchResult->getItems() as $product) {
            $productId = $product->getId();
            $stockItem = $this->stockItemFactory->create();
            /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $stockItemResource */
            $stockItemResource = $this->itemFactory->create();
            $stockItemResource->loadByProductId($stockItem, $product->getId(), $storeId);
            try {
                if (!$cart->getQuote()->hasProductId($productId) && floatval($productSkuArray[$product->getSku()])) {
                    $cart->addProduct($product->getId(), $productSkuArray[$product->getSku()]);
                    $successMessages[] = __('%1 was added to your shopping cart.', $this->escaper->escapeHtml($product->getName()));
                }
            } catch (\Exception $e) {
                $this->rejoinerHelper->log($e->getMessage());
            }
        }

        if (isset($params['coupon_code'])) {
            $cart->getQuote()->setCouponCode($params['coupon_code']);
        }

        try {
            $cart->save();
        }  catch (\Exception $e) {
            $this->rejoinerHelper->log($e->getMessage());
        }
        $this->checkoutSession->setCartWasUpdated(true);
        if ($successMessages) {
            foreach($successMessages as $message)
            $this->messageManager->addSuccess($message);
        }
        $url = $this->_url->getUrl('checkout/cart/', ['updateCart' => true]);
        $this->getResponse()->setRedirect($url);
        return $this->_response;
    }

}