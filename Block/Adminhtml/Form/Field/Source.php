<?php
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

class Source extends \Magento\Framework\View\Element\Html\Select
{

    protected $_metaSources = array(
        'utm_source' =>  'Campaign Source',
        'utm_medium'  =>  'Campaign Medium',
        'utm_campaign' =>  'Campaign Name',
    );


    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_metaSources as $groupId => $groupLabel) {
                $this->addOption($groupId, addslashes($groupLabel));
            }
        }
        return parent::_toHtml();
    }


    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

}