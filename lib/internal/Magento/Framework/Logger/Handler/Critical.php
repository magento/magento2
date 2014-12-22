<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Logger\Handler;

use Monolog\Logger;

class Critical extends System
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/exception.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::CRITICAL;
}
