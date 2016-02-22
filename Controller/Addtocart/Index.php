<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Controller\Addtocart;

use \Rejoiner\Acr\Helper\Data;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Checkout\Model\Cart;
use \Magento\Framework\App\Action\Action;

class Index extends Action
{
    /**
     * @var $_product \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var $rejoinerHelper Data
     */
    protected $rejoinerHelper;

    /**
     * @var $cart Cart
     */
    protected $cart;

    /**
     * @var $session Session
     */
    protected $session;

    /**
     * Index constructor.
     * @param Data $rejoinerHelper
     * @param Session $session
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Cart $cart
     */

    public function __construct(
        Data $rejoinerHelper,
        Session $session,
        Context $context,
        PageFactory $resultPageFactory,
        Cart $cart
    ) {
        $this->rejoinerHelper             = $rejoinerHelper;
        $this->resultPageFactory          = $resultPageFactory;
        $this->cart                       = $cart;
        $this->session                    = $session;
        parent::__construct($context);
    }

    /**
     *
     * Adds products to shopping cart

     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if ($params = $this->getRequest()->getParams()) {
            $cartModel = $this->cart;
            $cartModel->truncate();
            foreach ($params as $key => $product) {
                if ($product && is_array($product)) {
                    try {
                        $cartModel->addProduct($product['product'], $product);
                        unset($params[$key]);
                    } catch (\Exception $e) {
                        $this->rejoinerHelper->log($e->getMessage());
                    }
                }
            }
            if (isset($params['coupon_code'])) {
                $cartModel->getQuote()->setCouponCode($params['coupon_code']);
            }
            $cartModel->save();
            $this->session->setCartWasUpdated(true);
        }
        $url = $this->_url->getUrl('checkout/cart/', ['updateCart' => true]);
        $this->getResponse()->setRedirect($url);
        return $this->_response;
    }
}

