<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Framework\App\Backpressure\BackpressureExceededException;
use Magento\Framework\App\Backpressure\ContextInterface;

/**
 * Enforces certain backpressure
 */
interface BackpressureEnforcerInterface
{
    /**
     * Enforce the backpressure by throwing the exception when limit exceeded
     *
     * @param ContextInterface $context
     * @throws BackpressureExceededException
     * @return void
     */
    public function enforce(ContextInterface $context): void;
}
