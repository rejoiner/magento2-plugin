<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Controller\Addtocart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\CartFactory;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Rejoiner\Acr\Helper\Data;

class Index implements HttpGetActionInterface
{
    /** @var PageFactory $resultPageFactory */
    protected PageFactory $resultPageFactory;

    /** @var Data $rejoinerHelper */
    protected Data $rejoinerHelper;

    /** @var CartFactory $cartFactory */
    protected CartFactory $cartFactory;

    /** @var ProductFactory $productFactory */
    protected ProductFactory $productFactory;

    /** @var SessionFactory $sessionFactory */
    protected SessionFactory $sessionFactory;

    /** @var UrlInterface $urlBuilder */
    protected UrlInterface $urlBuilder;

    private RequestInterface $request;

    private Redirect $resultRedirect;

    private ProductRepositoryInterface $productRepository;

    /**
     * @param Data $rejoinerHelper
     * @param PageFactory $resultPageFactory
     * @param SessionFactory $sessionFactory
     * @param CartFactory $cartFactory
     * @param ProductFactory $productFactory
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Data $rejoinerHelper,
        PageFactory $resultPageFactory,
        SessionFactory $sessionFactory,
        CartFactory $cartFactory,
        ProductFactory $productFactory,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->rejoinerHelper    = $rejoinerHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->cartFactory       = $cartFactory;
        $this->sessionFactory    = $sessionFactory;
        $this->productFactory    = $productFactory;
        $this->urlBuilder        = $urlBuilder;
        $this->request           = $request;
        $this->resultRedirect    = $resultRedirectFactory->create();
        $this->productRepository = $productRepository;
    }

    /**
     * We assume if params in a querystring are array style (i.e. index[key]=value)
     * then they should represent cart product data. Otherwise, simple string
     * params should be persisted on redirect to the cart page to capture UTM
     * params, rjnrid, etc.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if ($params = $this->request->getParams()) {
            $cartModel = $this->cartFactory->create();
            $cartModel->truncate();

            foreach ($params as $key => $product) {
                if ($product && is_array($product)) {
                    try {
                        $productModel = $this->productRepository->getById($product['product']);
                        $cartModel->addProduct($productModel, $product);
                        unset($params[$key]);
                    } catch (\Exception $e) {
                        $this->rejoinerHelper->log($e->getMessage());
                    }
                }
            }

            if (isset($params['coupon_code'])) {
                $cartModel->getQuote()
                    ->setCouponCode($params['coupon_code'])
                    ->collectTotals();
            }

            $cartModel->save();
            $this->sessionFactory->create()->setCartWasUpdated(true);
        }

        $url = $this->urlBuilder->getUrl(
            'checkout/cart/',
            [
                'updateCart' => true,
                '_query' => $params
            ]
        );

        $this->resultRedirect->setUrl($url);

        return $this->resultRedirect;
    }
}
