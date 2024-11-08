<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class Source extends Select
{
    /** @var array $_metaSources */
    protected array $_metaSources = [
        'utm_source'   =>  'Campaign Source',
        'utm_medium'   =>  'Campaign Medium',
        'utm_campaign' =>  'Campaign Name'
    ];

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
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
    public function setInputName(string $value): self
    {
        return $this->setName($value);
    }
}
