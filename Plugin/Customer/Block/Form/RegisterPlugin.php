<?php

namespace Rejoiner\Acr\Plugin\Customer\Block\Form;

class RegisterPlugin
{
    /**
     * @var \Rejoiner\Acr\Helper\Data
     */
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
        return $result && $this->rejoinerHelper->getRejoinerMarketingPermissions() && $this->rejoinerHelper->getRejoinerSubscribeAccountRegistration();
    }
}