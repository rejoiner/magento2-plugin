<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Controller\Addtocart;

class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    protected $rejoinerHelper;

    /** @var \Magento\Checkout\Model\CartFactory $cartFactory */
    protected $cartFactory;

    /** @var \Magento\Catalog\Model\ProductFactory $productFactory */
    protected $productFactory;

    /** @var \Magento\Checkout\Model\SessionFactory $sessionFactory */
    protected $sessionFactory;

    /** @var \Magento\Framework\UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\SessionFactory $sessionFactory
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\SessionFactory $sessionFactory,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->rejoinerHelper    = $rejoinerHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->cartFactory       = $cartFactory;
        $this->sessionFactory    = $sessionFactory;
        $this->productFactory    = $productFactory;
        $this->urlBuilder        = $context->getUrl();
        parent::__construct($context);
    }

    /**
     * We assume if params in a querystring are array style (i.e. index[key]=value)
     * then they should represent cart product data. Otherwise, simple string
     * params should be persisted on redirect to the cart page to capture UTM
     * params, rjnrid, etc.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if ($params = $this->getRequest()->getParams()) {
            $cartModel = $this->cartFactory->create();
            $cartModel->truncate();

            foreach ($params as $key => $product) {
                if ($product && is_array($product)) {
                    $productModel = $this->productFactory->create();
                    $productModel->load((int)$product['product']);
                    try {
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
        $this->getResponse()->setRedirect($url);
        return $this->_response;
    }
}
