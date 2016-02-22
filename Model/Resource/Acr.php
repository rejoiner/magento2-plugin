<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Model\Resource;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Acr extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        //rejoiner_acr is table and entity_id is primary key of this table
        $this->_init('rejoiner_acr_successful_orders', 'entity_id');
    }
}