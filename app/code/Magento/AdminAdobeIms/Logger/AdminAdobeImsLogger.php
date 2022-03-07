<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Logger;

use DateTimeZone;
use Monolog\Logger;

class AdminAdobeImsLogger extends Logger
{
    /**
     * @var string
     */
    private string $enabled;

    /**
     * @param string $name
     * @param string $enabled
     * @param array $handlers
     * @param array $processors
     * @param DateTimeZone|null $timezone
     */
    public function __construct(
        string $name,
        string $enabled,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        parent::__construct($name, $handlers, $processors, $timezone);
        $this->enabled = $enabled;
    }

    /**
     * Log error message and check if logging is enabled
     *
     * @param $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = []): void
    {
        if ($this->enabled) {
            parent::error($message, $context);
        }
    }
}
