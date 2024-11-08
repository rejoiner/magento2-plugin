<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Model\ResourceModel\Acr;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Rejoiner\Acr\Model\Acr;
use Rejoiner\Acr\Model\ResourceModel\Acr as ResourceAcr;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Acr::class, ResourceAcr::class);
    }
}
