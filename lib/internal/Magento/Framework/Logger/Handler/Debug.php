<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Handler;

use Monolog\Logger;

class Debug extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/debug.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
