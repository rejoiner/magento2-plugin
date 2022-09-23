<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Logger;

use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/rejoiner_api.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
