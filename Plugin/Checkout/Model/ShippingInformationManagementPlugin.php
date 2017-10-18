<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Checkout\Model;

class ShippingInformationManagementPlugin
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
     * @param \Magento\Checkout\Model\ShippingInformationManagement $shippingInformationManagement
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return array
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $shippingInformationManagement,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        if ($this->rejoinerHelper->getRejoinerMarketingPermissions()) {
            /** @var \Magento\Quote\Api\Data\AddressExtension $extensionAttributes */
            $extensionAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();
            if ($extensionAttributes->getRejoinerSubscribe()) {
                $this->session->setRejoinerSubscribe(true);
                if ($email = $extensionAttributes->getRejoinerEmail()) {
                    /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
                    $subscriber = $this->subscriberFactory->create();
                    $subscriber->subscribe($email);
                }
            }
        }

        return [$cartId, $addressInformation];
    }
}