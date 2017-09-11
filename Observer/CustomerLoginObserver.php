<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

class CustomerLoginObserver implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    private $rejoinerHelper;

    /** @var \Magento\Framework\App\RequestInterface $request */
    private $request;

    /** @var \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory */
    private $subscriberFactory;

    /** @var \Magento\Customer\Model\Session $customerSession */
    private $customerSession;

    /** @var \Magento\Framework\Json\Helper\Data $jsonHelper */
    private $jsonHelper;

    /**
     * CustomerLoginObserver constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->request = $request;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->customerSession->isLoggedIn() && $this->rejoinerHelper->getRejoinerSubscribeLoginCheckout() && $this->isSubscribe()) {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $observer->getData('customer');
            /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
            $subscriber = $this->subscriberFactory->create();
            $subscriber->subscribe($customer->getEmail());
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
                $credentials = $this->jsonHelper->jsonDecode($this->request->getContent());
                $subscribe   = isset($credentials['rejoiner_subscription']) && $credentials['rejoiner_subscription'];
            } catch (\Exception $e) {}
        }

        return $subscribe;
    }
}