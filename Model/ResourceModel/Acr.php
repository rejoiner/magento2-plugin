<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Acr extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('rejoiner_acr_success_orders', 'entity_id');
    }
}
