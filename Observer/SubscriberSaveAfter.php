<?php
/**
 * Copyright © 2019 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Rejoiner\Acr\Observer;

/**
 * Newsletter Subscriber event observer adds user to Rejoiner list if enabled
 */
class SubscriberSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /*
     * @var \Rejoiner\Acr\Helper\Data $_rejoinerHelper
     */
    private $_rejoinerHelper;

    /**
     * Subscriber Observer constructor.
     *
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper Rejoiner data helper
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper
    ) {
        $this->_rejoinerHelper = $rejoinerHelper;
    }

    /**
     * Executes Rejoiner Add Customer to List on subscribe event
     *
     * @param \Magento\Framework\Event\Observer $observer Event Observer object
     *
     * @return null
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * Subscriber object from event
         *
         * @var \Magento\Newsletter\Model\Subscriber $subscriber
         */
        $subscriber = $observer->getSubscriber();

        if (!$subscriber->isSubscribed()) {
            return;
        }

        $email = $subscriber->getEmail();
        $firstName = $subscriber->getFirstname();

        $this->_rejoinerHelper->subscribe($email, $firstName);
    }
}