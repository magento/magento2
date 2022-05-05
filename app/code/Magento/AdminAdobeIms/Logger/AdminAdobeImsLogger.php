<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Logger;

use DateTimeZone;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Monolog\Logger;
use Stringable;

class AdminAdobeImsLogger extends Logger
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @param string $name
     * @param ImsConfig $imsConfig
     * @param array $handlers
     * @param array $processors
     * @param DateTimeZone|null $timezone
     */
    public function __construct(
        string $name,
        ImsConfig $imsConfig,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        parent::__construct($name, $handlers, $processors, $timezone);
        $this->imsConfig = $imsConfig;
    }

    /**
     * Log error message and check if logging is enabled
     *
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = []): void
    {
        if ($this->imsConfig->loggingEnabled()) {
            parent::error($message, $context);
        }
    }
}
