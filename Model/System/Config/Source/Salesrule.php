<?php
namespace Rejoiner\Acr\Model\System\Config\Source;

class Salesrule implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    private $ruleFactory;

    /**
     * Salesrule constructor.
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    public function __construct(\Magento\SalesRule\Model\RuleFactory $ruleFactory)
    {
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
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