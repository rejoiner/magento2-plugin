<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Acr
 * @package Rejoiner\Acr\Model
 *
 * @method int getEntityId()
 * @method int getOrderId()
 * @method string getCreatedAt()
 * @method string getSentAt()
 * @method int getResponseCode()
 * @method int setEntityId()
 * @method int setOrderId()
 * @method string setCreatedAt()
 * @method string setSentAt(string $value)
 * @method int setResponseCode(int $value)
 *
 */
class Acr extends AbstractModel
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
