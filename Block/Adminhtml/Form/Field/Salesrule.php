<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Salesrule extends Select
{
    /**
     * @var \Rejoiner\Acr\Model\System\Config\Source\Salesrule $ruleFactory
     */
    private \Rejoiner\Acr\Model\System\Config\Source\Salesrule $_salesRuleFactory;

    /**
     * @param \Rejoiner\Acr\Model\System\Config\Source\Salesrule $salesRuleFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Rejoiner\Acr\Model\System\Config\Source\Salesrule $salesRuleFactory,
        Context $context,
        array $data = []
    ) {
        $this->_salesRuleFactory = $salesRuleFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     * @throws LocalizedException
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $salesRuleOptions = $this->_salesRuleFactory->toOptionArray();
            foreach ($salesRuleOptions as $option) {
                $this->addOption($option['value'], addslashes((string)$option['label']));
            }
        }

        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value): self
    {
        return $this->setName($value);
    }
}
