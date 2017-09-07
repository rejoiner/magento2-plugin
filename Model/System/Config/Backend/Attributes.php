<?php

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
        $value = unserialize($value);
        if (is_array($value)) {
            $this->setValue($value);
        }
        return $this;
    }

}