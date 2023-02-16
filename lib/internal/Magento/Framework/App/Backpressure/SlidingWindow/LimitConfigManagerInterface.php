<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\ContextInterface;

/**
 * Provides limit configuration for request contexts
 */
interface LimitConfigManagerInterface
{
    /**
     * Find limits for given context
     *
     * @param ContextInterface $context
     * @return LimitConfig
     */
    public function readLimit(ContextInterface $context): LimitConfig;
}
