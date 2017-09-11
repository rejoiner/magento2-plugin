<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

class Google extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /** @var \Rejoiner\Acr\Block\Adminhtml\Form\Field\Source $sourceRenderer */
    protected $sourceRenderer;

    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'attr_name',
            [
                'label' => __('Attribute Name'),
                'renderer'  => $this->getSourceRenderer(),
            ]
        );
        $this->addColumn(
            'value',
            [
                'label' => __('Value'),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Rule');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $attrName = $row->getAttrName();
        $options = [];
        if ($attrName) {
            $options['option_' . $this->getSourceRenderer()->calcOptionHash($attrName)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);

        return;
    }

    /**
     * @return \Rejoiner\Acr\Block\Adminhtml\Form\Field\Source
     */
    protected function getSourceRenderer()
    {
        if (!$this->sourceRenderer) {
            $this->sourceRenderer = $this->getLayout()->createBlock(
                '\Rejoiner\Acr\Block\Adminhtml\Form\Field\Source',
                'google_anal',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->sourceRenderer;
    }
}