<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Newsletter\Model;

use Magento\Customer\Model\CustomerRegistry;
use Rejoiner\Acr\Helper\Data as RejoinerHelper;
use Magento\Newsletter\Model\Subscriber;

class SubscriberPlugin
{

    /**
     * SubscriberPlugin constructor.
     * @param RejoinerHelper $rejoinerHelper
     * @param CustomerRegistry $customerRegistry
     */
    public function __construct(
        private RejoinerHelper $rejoinerHelper,
        private CustomerRegistry $customerRegistry
    ) {
    }

    /**
     * @param Subscriber $subscriber
     */
    public function beforeSave(Subscriber $subscriber): void
    {
        if ($this->rejoinerHelper->getRejoinerMarketingPermissions() && $this->isStatusChanged($subscriber)) {
            try {
                if ($subscriber->getStatus() == Subscriber::STATUS_SUBSCRIBED) {
                    $customerName = '';
                    if ($customerId = $subscriber->getCustomerId()) {
                        $customer = $this->customerRegistry->retrieve($customerId);
                        $customerName = $customer->getData('firstname') ? $customer->getData('firstname') : '';
                    }

                    $this->rejoinerHelper->subscribe($subscriber->getEmail(), $customerName);
                    $subscriber->setData('added_to_rejoiner', RejoinerHelper::STATUS_SUBSCRIBED);
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
    private function isStatusChanged(Subscriber $subscriber): bool
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
