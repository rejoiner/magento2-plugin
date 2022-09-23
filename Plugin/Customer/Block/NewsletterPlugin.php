<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Customer\Block;

class NewsletterPlugin
{
    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    private $rejoinerHelper;

    /**
     * NewsletterPlugin constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     */
    public function __construct(\Rejoiner\Acr\Helper\Data $rejoinerHelper)
    {
        $this->rejoinerHelper = $rejoinerHelper;
    }

    /**
     * Update IsSubscribed flag
     *
     * @param \Magento\Customer\Block\Newsletter $subject
     * @param bool $result
     * @return bool
     */
    public function afterGetIsSubscribed(\Magento\Customer\Block\Newsletter $subject, $result)
    {
        if ($this->rejoinerHelper->getRejoinerMarketingPermissions()) {
            $result = false;
        }

        return $result;
    }
}
