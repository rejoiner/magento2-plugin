<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Model\System\Config\Source;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SalesRule\Model\RuleRepository;
use Magento\Framework\Option\ArrayInterface;

class Salesrule implements ArrayInterface
{
    /**
     * @var $ruleRepository RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var $searchCriteriaBuilder SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Salesrule constructor.
     * @param RuleRepository $ruleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RuleRepository $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->ruleRepository        = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('use_auto_generation', 1)->create();
        $collection = $this->ruleRepository->getList($searchCriteria);
        /** @var \Magento\SalesRule\Model\Data\Rule $item */
        foreach ($collection->getItems() as $item) {
            $options[] = [
                'value' => $item->getRuleId(),
                'label' => $item->getName()
            ];
        }
        array_unshift($options, array('value'=>'', 'label'=> __('--Please Select--')));
        return $options;
    }
}