<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Handler;

use Monolog\Logger;

class Debug extends System
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
