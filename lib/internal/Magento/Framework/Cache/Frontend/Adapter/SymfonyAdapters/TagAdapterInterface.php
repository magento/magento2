<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Interface for backend-specific tag operations
 *
 * This interface defines operations that different cache backends implement differently,
 * particularly for tag-based cache invalidation with AND logic (MATCHING_TAG mode).
 *
 * Implementations:
 * - RedisTagAdapter: Uses Redis SINTER for true AND logic
 * - FilesystemTagAdapter: Uses file-based tag indices with array_intersect
 * - GenericTagAdapter: Fallback using namespace tags or best-effort logic
 */
interface TagAdapterInterface
{
    /**
     * Get cache IDs that match ALL given tags (AND logic)
     *
     * This is used for CacheConstants::CLEANING_MODE_MATCHING_TAG
     *
     * @param array $tags Array of tags (must match ALL)
     * @return array Array of cache IDs
     */
    public function getIdsMatchingTags(array $tags): array;

    /**
     * Get cache IDs that match ANY of the given tags (OR logic)
     *
     * This is used for CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG
     *
     * @param array $tags Array of tags (match ANY)
     * @return array Array of cache IDs
     */
    public function getIdsMatchingAnyTags(array $tags): array;

    /**
     * Get cache IDs that do NOT match any of the given tags
     *
     * This is used for CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG
     *
     * @param array $tags Array of tags to exclude
     * @return array Array of cache IDs
     */
    public function getIdsNotMatchingTags(array $tags): array;

    /**
     * Delete cache items by their IDs
     *
     * @param array $ids Array of cache IDs to delete
     * @return bool True on success
     */
    public function deleteByIds(array $ids): bool;

    /**
     * Update tag-to-ID index when a cache item is saved
     *
     * @param string $id Cache ID
     * @param array $tags Tags associated with this ID
     * @return void
     */
    public function onSave(string $id, array $tags): void;

    /**
     * Update tag-to-ID index when a cache item is removed
     *
     * @param string $id Cache ID
     * @return void
     */
    public function onRemove(string $id): void;

    /**
     * Clear all tag indices (used for CLEANING_MODE_ALL)
     *
     * @return void
     */
    public function clearAllIndices(): void;

    /**
     * Sweep tag-index members whose data entry has expired (CLEANING_MODE_OLD).
     *
     * Redis/Valkey auto-expire the data key by TTL but leave the id in its tag SETs; this prunes
     * those orphaned members so the tag index does not grow unbounded. Mirrors legacy Cm Redis
     * _collectGarbage(), run by the backend_clean_cache cron. Adapters with no such index (file)
     * return 0.
     *
     * @param int $batchSize Number of ids to process per iteration
     * @return int Number of orphaned index entries removed
     */
    public function garbageCollect(int $batchSize = 1000): int;

    /**
     * Return the filling percentage of the underlying storage server, if the backend can report one.
     *
     * Mirrors legacy Cm_Cache_Backend_Redis::getFillingPercentage() (used_memory / maxmemory). Adapters
     * with no such server-level memory concept (filesystem, generic fallback) return 0.
     *
     * @return int Integer between 0 and 100
     */
    public function getFillingPercentage(): int;
}
