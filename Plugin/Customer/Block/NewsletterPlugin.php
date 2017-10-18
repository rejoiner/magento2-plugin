<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
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
     * @param \Magento\Customer\Block\Newsletter $subject
     * @param $result
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