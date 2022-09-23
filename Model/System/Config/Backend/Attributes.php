<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Model\System\Config\Backend;

class Attributes extends \Magento\Framework\App\Config\Value
{
    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        /** @var array $value */
        $value = $this->getValue();
        $value = is_array($value) ? $value : [];
        foreach ($value as $key => $data) {
            if (!$data) {
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
    protected function _afterLoad()
    {
        if ($value = $this->getValue()) {
            $value = unserialize($value, ['allowed_classes' => false]);
        }

        if (is_array($value)) {
            $this->setValue($value);
        }

        return $this;
    }
}
