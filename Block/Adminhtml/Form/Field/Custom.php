<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Custom extends AbstractFieldArray
{
    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender(): void
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
