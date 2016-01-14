<?php
namespace Rejoiner\Acr\Model;

class Acr extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Rejoiner\Acr\Model\Resource\Acr');
    }
}