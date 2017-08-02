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
 * @since 2.0.0
 */
class Debug extends Base
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $fileName = '/var/log/debug.log';

    /**
     * @var int
     * @since 2.0.0
     */
    protected $loggerType = Logger::DEBUG;
}
