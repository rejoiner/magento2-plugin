<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Sales\Model\AdminOrder;

use Magento\Newsletter\Model\SubscriptionManager;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;

class CreatePlugin
{

    /**
     * CreatePlugin constructor.
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(
        private readonly SubscriptionManager $subscriptionManager
    ) {
    }

    /**
     * @param Create $subject
     * @param Order $order
     * @return Order
     */
    public function afterCreateOrder(Create $subject, Order $order): Order
    {
        if ($subject->hasData('rejoiner_subscribe') && $subject->getData('rejoiner_subscribe')) {
            try {
                $email = $order->getCustomerEmail();
                $this->subscriptionManager->subscribe($email, $order->getStoreId());
            } catch (\Exception $e) {}
        }

        return $order;
    }
}
