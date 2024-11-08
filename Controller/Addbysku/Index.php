<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Controller\Addbysku;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\Checkout\Model\CartFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Rejoiner\Acr\Helper\Data;

class Index implements HttpGetActionInterface //extends Action
{
    /** @var Session $checkoutSession */
    protected Session $checkoutSession;

    /** @var Data $rejoinerHelper */
    protected Data $rejoinerHelper;

    /** @var CartFactory $cartFactory */
    protected CartFactory $cartFactory;

    /** @var StockItemInterfaceFactory $stockItemApiFactory */
    protected StockItemInterfaceFactory $stockItemApiFactory;

    /** @var ItemFactory $stockItemApiResourceFactory */
    protected ItemFactory $stockItemApiResourceFactory;

    /** @var StoreManagerInterface $storeManager */
    protected StoreManagerInterface $storeManager;

    /** @var ProductFactory $productFactory */
    protected ProductFactory $productFactory;

    /** @var Escaper $escaper */
    protected Escaper $escaper;

    /** @var RequestInterface $request */
    private RequestInterface $request;

    /** @var Redirect $resultRedirect */
    private Redirect $resultRedirect;

    /** @var UrlInterface $urlBuilder */
    private UrlInterface $urlBuilder;

    /** @var ManagerInterface $messageManager */
    private ManagerInterface $messageManager;

    private CartRepositoryInterface $cartRepository;

    /**
     * Index constructor.
     * @param Session $checkoutSession
     * @param Data $rejoinerHelper
     * @param CartFactory $cartFactory
     * @param ItemFactory $stockItemApiResourceFactory
     * @param StockItemInterfaceFactory $stockItemApiFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param Escaper $escaper
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Session $checkoutSession,
        Data $rejoinerHelper,
        CartFactory $cartFactory,
        ItemFactory $stockItemApiResourceFactory,
        StockItemInterfaceFactory $stockItemApiFactory,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        Escaper $escaper,
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        UrlInterface $urlBuilder,
        ManagerInterface $messageManager,
        CartRepositoryInterface $cartRepository
    ) {
        $this->checkoutSession       = $checkoutSession;
        $this->rejoinerHelper        = $rejoinerHelper;
        $this->cartFactory           = $cartFactory;
        $this->stockItemApiFactory   = $stockItemApiFactory;
        $this->stockItemApiResourceFactory = $stockItemApiResourceFactory;
        $this->storeManager          = $storeManager;
        $this->productFactory        = $productFactory;
        $this->escaper               = $escaper;
        $this->request               = $request;
        $this->resultRedirect        = $resultRedirectFactory->create();
        $this->urlBuilder            = $urlBuilder;
        $this->messageManager        = $messageManager;
        $this->cartRepository        = $cartRepository;
    }

    public function execute()
    {
        $params = $this->request->getParams();
        $cart = $this->cartFactory->create();
        $successMessage = '';
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        foreach ($params as $key => $product) {
            if ($product && is_array($product)) {
                $productModel = $this->productFactory->create();
                // loadByAttribute() return false if the product was not found. There is no need to check the ID,
                // but lets stay on the safe side for the future Magento releases
                /** @var Product $productBySKU */
                $productBySKU = $productModel->loadByAttribute('sku', $product['sku']);

                if (!$productBySKU || !$productId = $productBySKU->getId()) {
                    continue;
                }

                $stockItem = $this->stockItemApiFactory->create();
                $stockItemResource = $this->stockItemApiResourceFactory->create();
                $stockItemResource->loadByProductId($stockItem, $productId, $websiteId);
                $qty = $stockItem->getQty();

                try {
                    if (!$cart->getQuote()->hasProductId($productId) && is_numeric($product['qty']) && $qty > $product['qty']) {
                        $cart->addProduct($productBySKU, (int) $product['qty']);
                        $successMessage .= __('%1 was added to your shopping cart.'.'</br>', $this->escaper->escapeHtml($productBySKU->getName()));
                    }

                    unset($params[$key]);
                } catch (Exception $e) {
                    $this->rejoinerHelper->log($e->getMessage());
                }
            }
        }

        if (isset($params['coupon_code'])) {
            $cart->getQuote()->setCouponCode($params['coupon_code'])->collectTotals();
        }

        try {
            $this->cartRepository->save($cart->getQuote());
            $cart->save();
        }  catch (Exception $e) {
            $this->rejoinerHelper->log($e->getMessage());
        }

        $this->checkoutSession->setCartWasUpdated(true);

        if ($successMessage) {
            $this->messageManager->addSuccessMessage($successMessage);
        }

        $url = $this->urlBuilder->getUrl('checkout/cart/', ['updateCart' => true]);
        $this->resultRedirect->setUrl($url);

        return $this->resultRedirect;
    }
}
