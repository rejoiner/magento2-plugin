<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Model\System\Config\Backend;

use \Magento\Framework\App\Config\Value;


/**
 * Class Attributes
 * @package Rejoiner\Acr\Model\System\Config\Backend
 *
 * @method getValue()
 */

class Attributes extends Value
{
    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
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
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if ($value) {
            try {
                $value = unserialize($value);
                if (is_array($value)) {
                    $this->setValue($value);
                }
            } catch (\Exception $e) {
                $this->setValue([]);
            }
        }
        return $this;
    }

}