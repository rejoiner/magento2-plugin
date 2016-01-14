<?php
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

class Google extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{


    protected $_sourceRenderer;

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

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
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


    protected function _getSourceRenderer()
    {
        if (!$this->_sourceRenderer) {
            $this->_sourceRenderer = $this->getLayout()->createBlock(
                '\Rejoiner\Acr\Block\Adminhtml\Form\Field\Source',
                'google_anal',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_sourceRenderer;

    }


}