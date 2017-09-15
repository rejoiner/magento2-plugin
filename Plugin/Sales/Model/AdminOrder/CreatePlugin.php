<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Sales\Model\AdminOrder;

class CreatePlugin
{
    /**
     * @var \Rejoiner\Acr\Helper\Data
     */
    private $rejoinerHelper;

    /**
     * CreatePlugin constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     */
    public function __construct(\Rejoiner\Acr\Helper\Data $rejoinerHelper)
    {
        $this->rejoinerHelper = $rejoinerHelper;
    }

    /**
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Model\Order
     */
    public function afterCreateOrder(\Magento\Sales\Model\AdminOrder\Create $subject, \Magento\Sales\Model\Order $order)
    {
        if ($subject->hasData('rejoiner_subscribe') && $subject->getData('rejoiner_subscribe')) {
            try {
                $email        = $order->getCustomerEmail();
                $customerName = $order->getBillingAddress()->getFirstname();
                $this->rejoinerHelper->subscribe($email, $customerName);
            } catch (\Exception $e) {}
        }

        return $order;
    }
}