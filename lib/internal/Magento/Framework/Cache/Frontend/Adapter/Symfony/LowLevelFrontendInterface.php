<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\CacheConstants;

/**
 * Common low-level cache contract used by Symfony-backed Magento frontends.
 */
interface LowLevelFrontendInterface
{
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = []): bool;
}
