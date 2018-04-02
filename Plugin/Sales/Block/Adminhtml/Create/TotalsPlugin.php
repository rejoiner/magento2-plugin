<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Plugin\Sales\Block\Adminhtml\Create;

class TotalsPlugin
{
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Totals $subject
     * @param string $html
     * @return string
     */
    public function afterGetButtonHtml(\Magento\Sales\Block\Adminhtml\Order\Create\Totals $subject, $html)
    {
        return $subject->getChildHtml('rejoiner_subscribe') . $html;
    }
}