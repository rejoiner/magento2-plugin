<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Google extends AbstractFieldArray
{
    /** @var Source|null $sourceRenderer */
    protected ?Source $sourceRenderer = null;

    /**
     * Prepare to render
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
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
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $attrName = $row->getAttrName();
        $options = [];
        if ($attrName) {
            $options['option_' . $this->getSourceRenderer()->calcOptionHash($attrName)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return Source|null
     * @throws LocalizedException
     */
    protected function getSourceRenderer(): ?Source
    {
        if (!$this->sourceRenderer) {
            $this->sourceRenderer = $this->getLayout()->createBlock(
                Source::class,
                'google_anal',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->sourceRenderer;
    }
}
