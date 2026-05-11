<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Logger\Handler;

use Monolog\Logger;

class Exception extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/exception.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;
}
