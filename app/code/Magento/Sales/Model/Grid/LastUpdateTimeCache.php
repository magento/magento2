<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Grid;

use Magento\Framework\App\CacheInterface;

/**
 * Cache for last grid update time.
 */
class LastUpdateTimeCache
{
    /**
     * Prefix for cache key.
     */
    private const CACHE_PREFIX = 'LAST_GRID_UPDATE_TIME';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Save last grid update time.
     *
     * @param string $gridTableName
     * @param string $lastUpdatedAt
     * @return void
     */
    public function save(string $gridTableName, string $lastUpdatedAt): void
    {
        $this->cache->save(
            $lastUpdatedAt,
            $this->getCacheKey($gridTableName),
            [],
            3600
        );
    }

    /**
     * Get last grid update time.
     *
     * @param string $gridTableName
     * @return string|null
     */
    public function get(string $gridTableName): ?string
    {
        $lastUpdatedAt = $this->cache->load($this->getCacheKey($gridTableName));

        return $lastUpdatedAt ?: null;
    }

    /**
     * Remove last grid update time.
     *
     * @param string $gridTableName
     * @return void
     */
    public function remove(string $gridTableName): void
    {
        $this->cache->remove($this->getCacheKey($gridTableName));
    }

    /**
     * Generate cache key.
     *
     * @param string $gridTableName
     * @return string
     */
    private function getCacheKey(string $gridTableName): string
    {
        return self::CACHE_PREFIX . ':' . $gridTableName;
    }
}
