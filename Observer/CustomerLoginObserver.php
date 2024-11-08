<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Newsletter\Model\SubscriptionManager;
use Rejoiner\Acr\Helper\Data;

class CustomerLoginObserver implements \Magento\Framework\Event\ObserverInterface
{
    /** @var Data $rejoinerHelper */
    private Data $rejoinerHelper;

    /** @var RequestInterface $request */
    private RequestInterface $request;

    /** @var SubscriptionManager $subscriptionManager */
    private SubscriptionManager $subscriptionManager;

    /** @var Session $customerSession */
    private Session $customerSession;

    /** @var SerializerInterface $serializer */
    private SerializerInterface $serializer;

    /**
     * CustomerLoginObserver constructor.
     * @param Data $rejoinerHelper
     * @param RequestInterface $request
     * @param SubscriptionManager $subscriptionManager
     * @param Session $customerSession
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Data $rejoinerHelper,
        RequestInterface $request,
        SubscriptionManager $subscriptionManager,
        Session $customerSession,
        SerializerInterface $serializer
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->request = $request;
        $this->subscriptionManager = $subscriptionManager;
        $this->customerSession = $customerSession;
        $this->serializer = $serializer;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->customerSession->isLoggedIn() && $this->rejoinerHelper->getRejoinerSubscribeLoginCheckout() && $this->isSubscribe()) {
            $customer = $observer->getData('customer');
            $this->subscriptionManager->subscribe($customer->getEmail(), $customer->getStoreId());
        }
    }

    /**
     * @return bool
     */
    protected function isSubscribe()
    {
        $subscribe = false;
        if ($this->request->getParam('is_subscribed', false)) {
            $subscribe = true;
        } else {
            try {
                $credentials = $this->serializer->unserialize($this->request->getContent());
                $subscribe   = isset($credentials['rejoiner_subscription']) && $credentials['rejoiner_subscription'];
            } catch (\Exception $e) {}
        }

        return $subscribe;
    }
}
