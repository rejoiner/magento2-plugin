<?php

namespace Rejoiner\Acr\Plugin\Newsletter\Model;

use Rejoiner\Acr\Helper\Data as RejoinerHelper;
use Magento\Newsletter\Model\Subscriber;

class SubscriberPlugin
{
    /** @var RejoinerHelper $rejoinerHelper */
    private $rejoinerHelper;

    /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
    private $customerRegistry;

    /**
     * SubscriberPlugin constructor.
     * @param RejoinerHelper $rejoinerHelper
     * @param \Magento\Customer\Model\CustomerRegistry $customerRegistry
     */
    public function __construct(
        RejoinerHelper $rejoinerHelper,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * @param Subscriber $subscriber
     */
    public function beforeSave(Subscriber $subscriber)
    {
        if ($this->rejoinerHelper->getRejoinerMarketingPermissions() && $this->isStatusChanged($subscriber)) {
            try {
                if ($subscriber->getStatus() == Subscriber::STATUS_SUBSCRIBED) {
                    $customerName = '';
                    if ($customerId = $subscriber->getCustomerId()) {
                        /** @var \Magento\Customer\Model\Customer $customer */
                        $customer = $this->customerRegistry->retrieve($customerId);
                        $customerName = $customer->getData('firstname') ? $customer->getData('firstname') : '';
                    }

                    $this->rejoinerHelper->subscribe($subscriber->getEmail(), $customerName);
                    $subscriber->setData('added_to_rejoiner', RejoinerHelper::STATUS_SUBSCRIBED);
                } else {
                    $this->rejoinerHelper->unSubscribe($subscriber->getEmail());
                    $subscriber->setData('added_to_rejoiner', RejoinerHelper::STATUS_UNSUBSCRIBED);
                }
            } catch (\Exception $e) {
                $subscriber->setData('added_to_rejoiner', RejoinerHelper::STATUS_UNSUBSCRIBED);
            }
        }
    }

    /**
     * @param Subscriber $subscriber
     * @return bool
     */
    private function isStatusChanged(Subscriber $subscriber)
    {
        $changed = false;
        if ($subscriber->isStatusChanged()) {
            $changed = true;
        } elseif ($subscriber->getOrigData('subscriber_status')) {
            $changed = $subscriber->getStatus() != $subscriber->getOrigData('subscriber_status');
        }

        return $changed;
    }
}
