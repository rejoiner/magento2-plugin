<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Onepage\Success;

class Subscribe extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    private function getLastOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        $order = $this->getLastOrder();
        $data  = [
            'email'         => $order->getCustomerEmail(),
            'customer_name' => $order->getBillingAddress()->getFirstname()
        ];

        return \Zend_Json::encode($data);
    }

    /**
     * @return string
     */
    public function getSubscribeUrl()
    {
        return $this->getUrl('rejoiner/subscribe/index');
    }
}