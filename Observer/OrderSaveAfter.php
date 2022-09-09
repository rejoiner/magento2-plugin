<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    private $rejoinerHelper;

    /** @var \Magento\Checkout\Model\Session $session */
    private $session;

    /** @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory */
    private $subscriberFactory;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Checkout\Model\Session $session,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->session = $session;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->session->getData('rejoiner_subscribe')) {
            try {
                /** @var \Magento\Sales\Model\Order $order */
                $order = $observer->getData('order');

                /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
                $subscriber = $this->subscriberFactory->create();
                $subscriber->subscribe($order->getCustomerEmail());
            } catch (\Exception $e) {
            }

            $this->session->unsRejoinerSubscribe();
        }
    }
}
