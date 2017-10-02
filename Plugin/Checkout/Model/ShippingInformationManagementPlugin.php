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

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Checkout\Model\Session $session
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->session = $session;
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
            }
        }

        return [$cartId, $addressInformation];
    }
}