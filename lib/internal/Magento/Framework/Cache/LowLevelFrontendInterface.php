<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;

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

    /**
     * Return the tag adapter backing this frontend, or null when the frontend has none
     * (e.g. legacy Zend backends that expose no tag/index adapter).
     *
     * @return TagAdapterInterface|null
     */
    public function getTagAdapter(): ?TagAdapterInterface;
}
