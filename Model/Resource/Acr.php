<?php
namespace Rejoiner\Acr\Model\Resource;

class Acr extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        //rejoiner_acr is table and entity_id is primary key of this table
        $this->_init('rejoiner_acr_success_orders', 'entity_id');
    }
}