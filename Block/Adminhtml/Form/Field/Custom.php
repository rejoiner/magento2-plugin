<?php
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

class Custom extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
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
}