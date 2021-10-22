<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model\Backpressure;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfig;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfigManagerInterface;

/**
 * provides limits for checkout functionality.
 */
class CheckoutLimitConfigManager implements LimitConfigManagerInterface
{
    public const REQUEST_TYPE_ID = 'checkout';

    /**
     * @inheritDoc
     */
    public function readLimit(ContextInterface $context): LimitConfig
    {
        return new LimitConfig(3, 3600);
    }
}
