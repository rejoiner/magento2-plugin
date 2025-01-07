<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Sales\Block\Adminhtml\Create;

use Magento\Sales\Block\Adminhtml\Order\Create\Totals;

class TotalsPlugin
{
    /**
     * @param Totals $subject
     * @param string $html
     * @return string
     */
    public function afterGetButtonHtml(Totals $subject, string $html): string
    {
        return $subject->getChildHtml('rejoiner_subscribe') . $html;
    }
}
