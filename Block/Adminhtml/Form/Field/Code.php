<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

class Code extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Framework\View\Element\BlockInterface|null
     */
    protected $_salesruleRenderer;

    /**
     * @inheritDoc
     */
    protected function _prepareToRender()
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
              'renderer' => $this->_getSalesruleRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Code');
    }

    /**
     * Get Salesrule renderer
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getSalesruleRenderer()
    {
        if (!$this->_salesruleRenderer) {
            $this->_salesruleRenderer = $this->getLayout()->createBlock(
                \Rejoiner\Acr\Block\Adminhtml\Form\Field\Salesrule::class,
                'promo_salesrule',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->_salesruleRenderer;
    }

    /**
     * @inheritDoc
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $salesruleAttr = $row->getData('promo_salesrule');
        $options = [];
        if ($salesruleAttr) {
            $key = 'option_' . $this->_getSalesruleRenderer()->calcOptionHash($salesruleAttr);
            $options[$key] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
