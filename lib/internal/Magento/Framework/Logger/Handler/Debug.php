<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
