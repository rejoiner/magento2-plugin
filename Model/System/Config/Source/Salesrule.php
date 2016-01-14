<?php
namespace Rejoiner\Acr\Model\System\Config\Source;

class Salesrule implements \Magento\Framework\Option\ArrayInterface
{
    private $_ruleFactory;

    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory
    ) {
        $this->_ruleFactory = $ruleFactory;

    }

    public function toOptionArray()
    {
        $options   = array();
        $additional= array(
            'value' => 'rule_id',
            'label' => 'name'
        );

        $collection = $this->_ruleFactory->create()->getResourceCollection();
        foreach ($collection as $item) {
            if ($item->getUseAutoGeneration()) {
                $data = array();
                foreach ($additional as $code => $field) {
                    $data[$code] = $item->getData($field);
                }
                $options[] = $data;
            }

        }

        array_unshift($options, array('value'=>'', 'label'=> __('--Please Select--')));
        return $options;
    }
}