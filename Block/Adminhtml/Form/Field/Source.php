<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

use \Magento\Framework\View\Element\Html\Select;

/**
 * Class Source
 * @package Rejoiner\Acr\Block\Adminhtml\Form\Field
 *
 * @method string setName(string $value)
 */
class Source extends Select
{
    /**
     * @var array
     */
    protected $_metaSources = [
        'utm_source'   =>  'Campaign Source',
        'utm_medium'   =>  'Campaign Medium',
        'utm_campaign' =>  'Campaign Name',
    ];

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
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