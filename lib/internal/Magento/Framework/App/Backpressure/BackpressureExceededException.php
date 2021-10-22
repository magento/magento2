<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure;

/**
 * Thrown when backpressure is exceeded.
 */
class BackpressureExceededException extends \RuntimeException
{
    /**
     * @param \Throwable|null $prev
     * @param string $message
     * @param int $code
     */
    public function __construct(?\Throwable $prev = null, string $message = 'Backpressure exceeded', int $code = 0)
    {
        parent::__construct($message, $code, $prev);
    }
}
