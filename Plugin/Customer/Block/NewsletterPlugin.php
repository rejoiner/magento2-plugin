<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Customer\Block;

use Magento\Customer\Block\Newsletter;
use Rejoiner\Acr\Helper\Data;

class NewsletterPlugin
{
    /**
     * NewsletterPlugin constructor.
     * @param Data $rejoinerHelper
     */
    public function __construct(
        private readonly Data $rejoinerHelper
    ) {
    }

    /**
     * @param Newsletter $subject
     * @param $result
     * @return bool
     */
    public function afterGetIsSubscribed(Newsletter $subject, $result): bool
    {
        if ($this->rejoinerHelper->getRejoinerMarketingPermissions()) {
            $result = false;
        }

        return $result;
    }
}
