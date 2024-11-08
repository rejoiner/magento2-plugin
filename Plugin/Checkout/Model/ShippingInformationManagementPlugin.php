<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Rejoiner\Acr\Helper\Data;

class ShippingInformationManagementPlugin
{
    /** @var Data $rejoinerHelper */
    private $rejoinerHelper;

    /** @var Session $session */
    private $session;

    /** @var SubscriptionManagerInterface $subscriptionManager */
    private $subscriptionManager;


    /**
     * ShippingInformationManagementPlugin constructor.
     * @param Data $rejoinerHelper
     * @param Session $session
     * @param SubscriptionManagerInterface $subscriptionManager
     */
    public function __construct(
        Data                         $rejoinerHelper,
        Session                      $session,
        SubscriptionManagerInterface $subscriptionManager
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->session = $session;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @param ShippingInformationManagement $shippingInformationManagement
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $shippingInformationManagement,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if ($this->rejoinerHelper->getRejoinerMarketingPermissions()) {
            /** @var \Magento\Quote\Api\Data\AddressExtension $extensionAttributes */
            $extensionAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();

            if ($extensionAttributes->getRejoinerSubscribe()) {
                $this->session->setRejoinerSubscribe(true);

                if ($email = $extensionAttributes->getRejoinerEmail()) {
                    $this->subscriptionManager->subscribe($email, $this->session->getQuote()->getStoreId());
                }
            }
        }

        return [$cartId, $addressInformation];
    }
}
