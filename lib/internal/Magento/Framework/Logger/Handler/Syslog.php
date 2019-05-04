<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Handler;

use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

/**
 * @inheritdoc
 */
class Syslog extends SyslogHandler
{
    private const FACILITY = LOG_USER;
    private const LEVEL = Logger::DEBUG;

    /**
     * @param string $ident The string ident to be added to each message
     */
    public function __construct(string $ident)
    {
        parent::__construct($ident, self::FACILITY, self::LEVEL);
    }
}
