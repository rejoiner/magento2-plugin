<?php

namespace Rejoiner\Acr\Model\ResourceModel;

class Acr extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rejoiner_acr_success_orders', 'entity_id');
    }
}