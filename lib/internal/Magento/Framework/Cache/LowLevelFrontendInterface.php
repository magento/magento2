<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

/**
 * Common low-level cache contract used by Magento cache consumers.
 */
interface LowLevelFrontendInterface
{
    /**
     * Clean cache entries using Magento-compatible cleaning modes.
     *
     * @param string $mode
     * @param array|string $tags
     * @return bool
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, $tags = []);
}
