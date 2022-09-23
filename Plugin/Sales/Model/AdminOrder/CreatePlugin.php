<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Sales\Model\AdminOrder;

class CreatePlugin
{
    /** @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory */
    private $subscriberFactory;

    /**
     * CreatePlugin constructor.
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(\Magento\Newsletter\Model\SubscriberFactory $subscriberFactory)
    {
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Subscribe customer after order has been created
     *
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Sales\Model\Order
     */
    public function afterCreateOrder(\Magento\Sales\Model\AdminOrder\Create $subject, \Magento\Sales\Model\Order $order)
    {
        if ($subject->hasData('rejoiner_subscribe') && $subject->getData('rejoiner_subscribe')) {
            try {
                $email = $order->getCustomerEmail();
                /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
                $subscriber = $this->subscriberFactory->create();
                $subscriber->subscribe($email);
            } catch (\Exception $e) {
            }
        }

        return $order;
    }
}
