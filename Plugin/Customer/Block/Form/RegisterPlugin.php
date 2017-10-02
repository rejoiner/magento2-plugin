<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Customer\Block\Form;

class RegisterPlugin
{
    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    protected $rejoinerHelper;

    /**
     * RegisterPlugin constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     */
    public function __construct(\Rejoiner\Acr\Helper\Data $rejoinerHelper)
    {
        $this->rejoinerHelper = $rejoinerHelper;
    }

    /**
     * @param \Magento\Customer\Block\Form\Register $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsNewsletterEnabled(\Magento\Customer\Block\Form\Register $subject, $result)
    {
        if ($result && $this->rejoinerHelper->getRejoinerMarketingPermissions()) {
            $result = $this->rejoinerHelper->getRejoinerSubscribeAccountRegistration();
        }

        return $result;
    }

    /**
     * @param \Magento\Customer\Block\Form\Register $subject
     * @param \Magento\Framework\DataObject $result
     * @return \Magento\Framework\DataObject
     */
    public function afterGetFormData(\Magento\Customer\Block\Form\Register $subject, $result)
    {
        if ($this->rejoinerHelper->getRejoinerMarketingPermissions()) {
            $result->setData('is_subscribed', $this->rejoinerHelper->getRejoinerSubscribeCheckedDefault());
        }

        return $result;
    }
}