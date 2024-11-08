<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Model\System\Config\Backend;

use Magento\Framework\App\Config\Value;

class Attributes extends Value
{
    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave(): self
    {
        /** @var array $value */
        $value = $this->getValue();
        $value = is_array($value) ? $value : [];

        foreach ($value as $key => $data) {
            if (!$data ) {
                unset($value[$key]);
            }
        }

        $this->setValue(serialize($value));

        return $this;
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    protected function _afterLoad(): self
    {
        $value = (string)$this->getValue();
        $value = unserialize($value);

        if (is_array($value)) {
            $this->setValue($value);
        }

        return $this;
    }
}
