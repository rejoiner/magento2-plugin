<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\SubscriptionManager;
use Rejoiner\Acr\Helper\Data;

class OrderSaveAfter implements ObserverInterface
{
    /** @var Data $rejoinerHelper */
    private Data $rejoinerHelper;

    /** @var Session $session */
    private Session $session;

    /** @var SubscriptionManager $subscriptionManager */
    private SubscriptionManager $subscriptionManager;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param Data $rejoinerHelper
     * @param Session $session
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(
        Data $rejoinerHelper,
        Session $session,
        SubscriptionManager $subscriptionManager
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->session = $session;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->session->getData('rejoiner_subscribe')) {
            try {
                $order = $observer->getData('order');
                $this->subscriptionManager->subscribe($order->getCustomerEmail(), $order->getStoreId());
            } catch (Exception $e) {}
            $this->session->unsRejoinerSubscribe();
        }
    }
}
