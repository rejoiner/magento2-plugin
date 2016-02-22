<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

use Magento\Framework\DataObject;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Google
 * @package Rejoiner\Acr\Block\Adminhtml\Form\Field
 *
 * @method string getSourceRendererBlockType()
 */
class Google extends AbstractFieldArray
{
    /** @var \Magento\Framework\View\Element\Html\Select $sourceRenderer */
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
                'renderer'  => $this->_getSourceRenderer(),
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
    protected function _prepareArrayRow(DataObject $row)
    {
        $attrName = $row->getAttrName();
        $options = [];
        if ($attrName) {
            $options['option_' . $this->_getSourceRenderer()->calcOptionHash($attrName)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
        return;
    }

    /**
     * @return \Magento\Framework\View\Element\Html\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getSourceRenderer()
    {
        if (!$this->sourceRenderer) {
            $this->sourceRenderer = $this->getLayout()->createBlock(
                $this->getSourceRendererBlockType(),
                'google_anal',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->sourceRenderer;
    }
}