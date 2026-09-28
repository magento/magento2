<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestModuleWebapiBackpressure\Model;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfig;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfigManagerInterface;

class LimitConfigManager implements LimitConfigManagerInterface
{
    /**
     * @inheritDoc
     */
    public function readLimit(ContextInterface $context): LimitConfig
    {
        return new LimitConfig(2, 60);
    }
}
