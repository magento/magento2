<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger;

use DateTimeZone;
use Monolog\Logger;

/**
 * Monolog Logger
 */
class Monolog extends Logger
{
    /**
     * @inheritdoc
     */
    public function __construct(
        string $name,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        /**
         * TODO: This should be eliminated with MAGETWO-53989
         */
        $handlers = array_values($handlers);

        parent::__construct($name, $handlers, $processors, $timezone);
    }
}
