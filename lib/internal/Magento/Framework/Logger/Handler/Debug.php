<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Handler;

use Monolog\Logger;

/**
 * Class \Magento\Framework\Logger\Handler\Debug
 *
 */
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
