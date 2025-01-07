<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Customer\Block\Form;

use Magento\Customer\Block\Form\Register;
use Rejoiner\Acr\Helper\Data;

class RegisterPlugin
{
    /**
     * RegisterPlugin constructor.
     * @param Data $rejoinerHelper
     */
    public function __construct(
        protected Data $rejoinerHelper
    ) {
    }

    /**
     * @param Register $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsNewsletterEnabled(Register $subject, bool $result): bool
    {
        if ($result && $this->rejoinerHelper->getRejoinerMarketingPermissions()) {
            $result = $this->rejoinerHelper->getRejoinerSubscribeAccountRegistration();
        }

        return $result;
    }

    /**
     * @param Register $subject
     * @param \Magento\Framework\DataObject $result
     * @return \Magento\Framework\DataObject
     */
    public function afterGetFormData(Register $subject, $result)
    {
        if ($this->rejoinerHelper->getRejoinerMarketingPermissions()) {
            $result->setData('is_subscribed', $this->rejoinerHelper->getRejoinerSubscribeCheckedDefault());
        }

        return $result;
    }
}
