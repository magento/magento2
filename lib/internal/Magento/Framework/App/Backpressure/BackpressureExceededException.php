<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure;

/**
 * Thrown when backpressure is exceeded
 */
class BackpressureExceededException extends \RuntimeException
{
}
