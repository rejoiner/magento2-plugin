<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Controller\Addbysku;

class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /** @var \Magento\Checkout\Model\Session $checkoutSession */
    protected $checkoutSession;

    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    protected $rejoinerHelper;

    /** @var \Magento\Checkout\Model\CartFactory $cartFactory */
    protected $cartFactory;

    /** @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemApiFactory */
    protected $stockItemApiFactory;

    /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockItemApiResourceFactory */
    protected $stockItemApiResourceFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
    protected $storeManager;

    /** @var \Magento\Catalog\Model\ProductFactory $productFactory */
    protected $productFactory;

    /** @var \Magento\Framework\Escaper $escaper */
    protected $escaper;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockItemApiResourceFactory
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemApiFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockItemApiResourceFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemApiFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->scopeConfig           = $scopeConfig;
        $this->checkoutSession       = $checkoutSession;
        $this->rejoinerHelper        = $rejoinerHelper;
        $this->cartFactory           = $cartFactory;
        $this->stockItemApiFactory   = $stockItemApiFactory;
        $this->stockItemApiResourceFactory = $stockItemApiResourceFactory;
        $this->storeManager          = $storeManager;
        $this->productFactory        = $productFactory;
        $this->escaper               = $escaper;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $this->cartFactory->create();
        $successMessage = '';
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        foreach ($params as $key => $product) {
            if ($product && is_array($product)) {
                $productModel = $this->productFactory->create();
                // loadByAttribute() return false if the product was not found. There is no need to check the ID,
                // but lets stay on the safe side for the future Magento releases
                /** @var \Magento\Catalog\Model\Product $productBySKU */
                $productBySKU = $productModel->loadByAttribute('sku', $product['sku']);
                if (!$productBySKU || !$productId = $productBySKU->getId()) {
                    continue;
                }
                $stockItem = $this->stockItemApiFactory->create();
                /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $stockItemResource */
                $stockItemResource = $this->stockItemApiResourceFactory->create();
                $stockItemResource->loadByProductId($stockItem, $productId, $websiteId);
                $qty = $stockItem->getQty();
                try {
                    if (!$cart->getQuote()->hasProductId($productId) && is_numeric($product['qty']) && $qty > $product['qty']) {
                        $cart->addProduct($productBySKU, (int) $product['qty']);
                        $successMessage .= __('%1 was added to your shopping cart.'.'</br>', $this->escaper->escapeHtml($productBySKU->getName()));
                    }
                    unset($params[$key]);
                } catch (\Exception $e) {
                    $this->rejoinerHelper->log($e->getMessage());
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
            $this->rejoinerHelper->log($e->getMessage());
        }

        $this->checkoutSession->setCartWasUpdated(true);

        if ($successMessage) {
            $this->messageManager->addSuccess($successMessage);
        }
        $url = $this->_url->getUrl('checkout/cart/', ['updateCart' => true]);
        $this->getResponse()->setRedirect($url);
    }
}