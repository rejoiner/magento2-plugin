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
     * @param $label
     * @param $onclick
     * @param string $class
     * @param null $buttonId
     * @param array $dataAttr
     * @return array
     */
    public function beforeGetButtonHtml(
        \Magento\Sales\Block\Adminhtml\Order\Create\Totals $subject,
        $label,
        $onclick,
        $class = '',
        $buttonId = null,
        $dataAttr = []
    ) {
        echo $subject->getChildHtml('rejoiner_subscribe');

        return [$label, $onclick, $class, $buttonId, $dataAttr];
    }
}