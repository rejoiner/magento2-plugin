<?php
declare(strict_types=1);
/**
 * Copyright © 2019 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Code extends AbstractFieldArray
{
    protected ?Salesrule $_salesRuleRenderer = null;

    /**
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn(
            'promo_param',
            [
              'label' => __('Parameter Name'),
            ]
        );
        $this->addColumn(
            'promo_salesrule',
            [
              'label' => __('Sales Rule'),
              'renderer' => $this->_getSalesRuleRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Code');
    }

    /**
     * @throws LocalizedException
     */
    protected function _getSalesRuleRenderer(): ?Salesrule
    {
        if (!$this->_salesRuleRenderer) {
            $this->_salesRuleRenderer = $this->getLayout()->createBlock(
                Salesrule::class,
                'promo_salesrule',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->_salesRuleRenderer;
    }

    /**
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $salesRuleAttr = $row->getData('promo_salesrule');
        $options = [];

        if ($salesRuleAttr) {
            $key = 'option_' . $this->_getSalesruleRenderer()->calcOptionHash($salesRuleAttr);
            $options[$key] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
}
