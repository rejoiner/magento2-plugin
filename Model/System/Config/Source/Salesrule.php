<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Model\System\Config\Source;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Option\ArrayInterface;
use Magento\SalesRule\Model\RuleFactory;

class Salesrule implements ArrayInterface
{
    /**
     * @var RuleFactory $ruleFactory
     */
    private RuleFactory $ruleFactory;

    /**
     * Salesrule constructor.
     * @param RuleFactory $ruleFactory
     */
    public function __construct(RuleFactory $ruleFactory)
    {
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray(): array
    {
        $options = [];
        $additional = [
            'value' => 'rule_id',
            'label' => 'name'
        ];

        $collection = $this->ruleFactory->create()->getResourceCollection();

        foreach ($collection as $item) {
            if ($item->getUseAutoGeneration()) {
                $data = [];

                foreach ($additional as $code => $field) {
                    $data[$code] = $item->getData($field);
                }

                $options[] = $data;
            }
        }

        array_unshift($options, ['value'=>'', 'label'=> __('--Please Select--')]);

        return $options;
    }
}
