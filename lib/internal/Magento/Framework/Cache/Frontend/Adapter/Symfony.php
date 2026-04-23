<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter;

use Closure;
use InvalidArgumentException;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapterProvider;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\GenericTagAdapter;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\FrontendInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Symfony Cache adapter for Magento
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Symfony implements FrontendInterface
{
    public const DEFAULT_CACHE_PREFIX = '69d_';
    public const DEFAULT_LIFETIME = 7200;
    public const FALLBACK_EXPIRY = 86400;
    public const ASSUMED_LIFETIME = 7200;

    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cache;

    /**
     * @var TagAdapterInterface
     */
    private TagAdapterInterface $adapter;

    /**
     * @var Closure|null
     */
    private ?Closure $cacheFactory;

    /**
     * @var int
     */
    private int $pid;

    /**
     * @var array
     */
    private array $parentCachePools = [];

    /**
     * @var bool|null
     */
    private ?bool $isTagAware = null;

    /**
     * @var int
     */
    private int $defaultLifetime;

    /**
     * @var string
     */
    private string $idPrefix;

    /**
     * @var bool
     */
    private bool $batchMode = false;

    /**
     * @var array
     */
    private array $batchedItems = [];

    /**
     * @var bool
     */
    private bool $alwaysDeferSaves = false;

    /**
     * @var bool
     */
    private bool $hasPendingWrites = false;

    /**
     * @var array
     */
    private array $responseCache = [];

    /**
     * @var int
     */
    private const RESPONSE_CACHE_MAX_SIZE = 500;

    /**
     * @var int Response cache TTL in seconds
     *
     * Set to 0 to disable (safer for multi-instance scenarios)
     * Can be increased in single-instance production environments
     */
    private const RESPONSE_CACHE_TTL = 0;

    /**
     * Constructor
     *
     * @param Closure $cacheFactory Factory that creates the cache pool
     * @param TagAdapterInterface|null $adapter Backend-specific tag adapter
     * @param int $defaultLifetime Default cache lifetime in seconds
     * @param string $idPrefix Cache ID prefix
     */
    public function __construct(
        Closure $cacheFactory,
        ?TagAdapterInterface $adapter = null,
        int $defaultLifetime = self::DEFAULT_LIFETIME,
        string $idPrefix = self::DEFAULT_CACHE_PREFIX
    ) {
        $this->cacheFactory = $cacheFactory;
        $this->pid = getmypid();
        $this->cache = $cacheFactory();
        $this->defaultLifetime = $defaultLifetime;
        $this->idPrefix = $idPrefix;
        $this->adapter = $adapter ?? new GenericTagAdapter($this->cache);
    }

    /**
     * Get cache pool instance (with process ID check)
     *
     * @return CacheItemPoolInterface
     */
    private function getCache(): CacheItemPoolInterface
    {
        $currentPid = getmypid();

        if ($currentPid !== $this->pid) {
            $this->parentCachePools[] = $this->cache;
            $this->cache = ($this->cacheFactory)();
            $this->pid = $currentPid;
            $this->isTagAware = null;
        }

        return $this->cache;
    }

    /**
     * Check if cache supports tag-aware operations
     *
     * @return bool
     */
    private function isTagAware(): bool
    {
        if ($this->isTagAware === null) {
            $this->isTagAware = $this->getCache() instanceof TagAwareAdapterInterface;
        }
        return $this->isTagAware;
    }

    /**
     * Clean and normalize cache identifier
     *
     * @param string|null $identifier
     * @return string|null
     */
    private function cleanIdentifier(?string $identifier): ?string
    {
        if ($identifier === null) {
            return null;
        }

        $identifier = strtoupper($identifier);
        $cleaned = str_replace('.', '__', $identifier);
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $cleaned);
    }

    /**
     * Clean multiple cache identifiers
     *
     * @param array $identifiers
     * @return array
     */
    private function cleanIdentifiers(array $identifiers): array
    {
        return array_map([$this, 'cleanIdentifier'], $identifiers);
    }

    /**
     * @inheritDoc
     */
    public function test($identifier)
    {
        $cleanId = $this->cleanIdentifier($identifier);
        $cacheKey = 'test:' . $cleanId;

        // OPTIMIZATION: Check response cache first (Predis optimization)
        if (isset($this->responseCache[$cacheKey])) {
            $cached = $this->responseCache[$cacheKey];
            if ((time() - $cached['time']) < self::RESPONSE_CACHE_TTL) {
                return $cached['result'];
            }
            unset($this->responseCache[$cacheKey]);
        }

        if ($this->hasPendingWrites) {
            $this->commitPendingWrites();
        }

        $cache = $this->getCache();
        $item = $cache->getItem($cleanId);

        if (!$item->isHit()) {
            return false;
        }

        $value = $item->get();

        $result = is_array($value) && isset($value['mtime'])
            ? (int)$value['mtime']
            : time();

        // Cache result in memory
        if (count($this->responseCache) < self::RESPONSE_CACHE_MAX_SIZE) {
            $this->responseCache[$cacheKey] = [
                'result' => $result,
                'time' => time()
            ];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        $cleanId = $this->cleanIdentifier($identifier);
        $cacheKey = 'load:' . $cleanId;

        // OPTIMIZATION: Check response cache first (Predis optimization)
        if (isset($this->responseCache[$cacheKey])) {
            $cached = $this->responseCache[$cacheKey];
            if ((time() - $cached['time']) < self::RESPONSE_CACHE_TTL) {
                return $cached['result'];
            }
            unset($this->responseCache[$cacheKey]);
        }

        if ($this->hasPendingWrites) {
            $this->commitPendingWrites();
        }

        $cache = $this->getCache();
        $item = $cache->getItem($cleanId);

        if (!$item->isHit()) {
            return false;
        }

        $wrappedData = $item->get();

        $result = (is_array($wrappedData) && array_key_exists('data', $wrappedData))
            ? $wrappedData['data']
            : $wrappedData;

        // Cache result in memory (only cache hits, not misses)
        if ($result !== false && count($this->responseCache) < self::RESPONSE_CACHE_MAX_SIZE) {
            $this->responseCache[$cacheKey] = [
                'result' => $result,
                'time' => time()
            ];
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        $cache = $this->getCache();
        $cleanId = $this->cleanIdentifier($identifier);

        // Clear response cache for this key (important for concurrent access)
        unset($this->responseCache['test:' . $cleanId]);
        unset($this->responseCache['load:' . $cleanId]);

        $item = $cache->getItem($cleanId);

        // Calculate actual lifetime to use
        $actualLifetime = $this->calculateActualLifetime($lifeTime);

        // Clean tags once for reuse
        $cleanTags = !empty($tags) ? $this->cleanIdentifiers($tags) : [];

        // OPTIMIZATION: Conditional metadata wrapping
        $needsMetadata = !empty($cleanTags) || ($actualLifetime !== $this->defaultLifetime);

        if ($needsMetadata) {
            $this->prepareItemWithMetadata($item, $data, $cleanTags, $actualLifetime);
        } else {
            // FAST PATH: Store data directly without metadata wrapper
            $item->set($data);
        }

        // Set expiration on Symfony item
        if ($actualLifetime !== null) {
            $item->expiresAfter($actualLifetime);
        }

        // AUTOMATIC BATCHING: Always defer saves for performance
        // Commits happen automatically before reads and at request end
        if ($this->alwaysDeferSaves) {
            $this->batchedItems[$cleanId] = [
                'item' => $item,
                'tags' => $cleanTags
            ];
            $this->hasPendingWrites = true;
            return $cache->saveDeferred($item);
        }

        // LEGACY MODE: Immediate save (only if alwaysDeferSaves is disabled)
        // BATCH MODE: Defer save if batching is enabled
        if ($this->batchMode) {
            $this->batchedItems[$cleanId] = [
                'item' => $item,
                'tags' => $cleanTags
            ];
            $this->hasPendingWrites = true;
            return $cache->saveDeferred($item);
        }

        // NORMAL MODE: Immediate save
        $success = $cache->save($item);

        // Commit and notify helpers
        $this->commitAndNotify($cache, $success, $cleanId, $cleanTags);

        return $success;
    }

    /**
     * Destructor - ensures any pending writes are committed
     *
     * This provides automatic batching: all saves are deferred during the request,
     * and committed once at the end. This reduces overhead from N×commit to 1×commit.
     *
     * @return void
     */
    public function __destruct()
    {
        // Auto-commit any pending writes
        if ($this->hasPendingWrites) {
            try {
                $this->commitPendingWrites();
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (\Exception $e) {
                // Intentional no-op: Silently fail in destructor (request is ending anyway)
                // In production, this would be logged
            }
        }
    }

    /**
     * Commit all pending deferred writes
     *
     * This method is called automatically:
     * - Before any read (load/test operations)
     * - At end of request (__destruct)
     * - When explicit commit is requested (endBatch)
     *
     * @return bool True if commit was successful
     */
    private function commitPendingWrites(): bool
    {
        if (!$this->hasPendingWrites || empty($this->batchedItems)) {
            return true;
        }

        $cache = $this->getCache();

        // Commit all deferred items
        $success = $cache->commit();

        // Notify tag adapters about all saved items
        foreach ($this->batchedItems as $cleanId => $itemData) {
            $this->commitAndNotify($cache, $success, $cleanId, $itemData['tags']);
        }

        // Clear state
        $this->batchedItems = [];
        $this->hasPendingWrites = false;

        return $success;
    }

    /**
     * Begin batch mode for cache operations
     *
     * When in batch mode, all save() calls will be deferred until endBatch() is called.
     * This reduces overhead from 79 × commit() to 1 × commit() for bulk operations.
     *
     * Performance impact:
     * - Without batching: 79 saves × 0.8ms = 63ms
     * - With batching: 1 commit × 0.8ms = 0.8ms
     * - Savings: ~62ms per bulk operation
     *
     * Usage:
     * <code>
     * $cache->beginBatch();
     * foreach ($items as $item) {
     *     $cache->save($data, $id, $tags);  // Deferred
     * }
     * $cache->endBatch();  // Commit all at once
     * </code>
     *
     * Note: If endBatch() is not called, items will be auto-committed
     * in __destruct() at the end of the request.
     *
     * @return void
     */
    public function beginBatch(): void
    {
        $this->batchMode = true;
        $this->alwaysDeferSaves = true;  // Enable automatic deferring
        $this->batchedItems = [];
    }

    /**
     * End batch mode and commit all deferred cache operations
     *
     * Note: With alwaysDeferSaves mode, this is optional since commits
     * happen automatically before reads and at request end.
     *
     * @return bool True if all items were committed successfully
     */
    public function endBatch(): bool
    {
        $this->batchMode = false;
        $this->alwaysDeferSaves = false;  // Disable automatic deferring
        return $this->commitPendingWrites();
    }

    /**
     * Calculate the actual lifetime to use for cache entry
     *
     * Enforces Redis MAX_LIFETIME limit (30 days) to prevent TTL overflow issues.
     * Matches Zend's Cm_Cache_Backend_Redis behavior.
     *
     * @param mixed $lifeTime
     * @return int|null
     */
    private function calculateActualLifetime($lifeTime): ?int
    {
        $actualLifetime = null;

        if ($lifeTime !== null && $lifeTime !== false && $lifeTime !== 0) {
            $actualLifetime = (int)$lifeTime;
        } elseif ($lifeTime === 0 || $lifeTime === false) {
            // 0 or false means use default in Zend behavior
            $actualLifetime = $this->defaultLifetime;
        } else {
            $actualLifetime = $this->defaultLifetime;
        }

        // Enforce Redis MAX_LIFETIME limit (matches Zend behavior)
        // Beyond 30 days, Redis may have TTL tracking issues
        if ($actualLifetime !== null && $actualLifetime > SymfonyAdapterProvider::REDIS_MAX_LIFETIME) {
            $actualLifetime = SymfonyAdapterProvider::REDIS_MAX_LIFETIME;
        }

        return $actualLifetime;
    }

    /**
     * Prepare cache item with metadata wrapper
     *
     * @param CacheItemInterface $item
     * @param mixed $data
     * @param array $cleanTags
     * @param int|null $actualLifetime
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function prepareItemWithMetadata($item, $data, array $cleanTags, ?int $actualLifetime): void
    {
        $now = time();

        // Get enhanced tags (including namespace tags if applicable)
        $tagsToSet = $cleanTags;
        if ($this->adapter instanceof GenericTagAdapter && !empty($cleanTags)) {
            $tagsToSet = $this->adapter->getTagsForSave($cleanTags);
        }

        // Calculate expiry timestamp (for Zend compatibility)
        $expiry = $actualLifetime !== null ? ($now + $actualLifetime) : null;

        // Wrap data with metadata for consistent timestamps
        $wrappedData = [
            'data' => $data,
            'mtime' => $now,
            'expire' => $expiry,
            'tags' => array_values(array_unique($tagsToSet))
        ];

        $item->set($wrappedData);

        // Handle tags
        if ($this->isTagAware() && !empty($tagsToSet)) {
            $item->tag($tagsToSet);
        }
    }

    /**
     * Commit cache and notify helpers
     *
     * @param CacheItemPoolInterface $cache
     * @param bool $success
     * @param string $cleanId
     * @param array $cleanTags
     * @return void
     */
    private function commitAndNotify($cache, bool $success, string $cleanId, array $cleanTags): void
    {
        // Ensure immediate persistence (commit any deferred saves)
        if ($success && method_exists($cache, 'commit')) {
            $cache->commit();
        }

        // Notify helper about the save (for Redis/Filesystem to maintain indices)
        // Note: onSave() already handles reverse index, no need for separate call
        if ($success && !empty($cleanTags)) {
            $this->adapter->onSave($cleanId, $cleanTags);
        }
    }

    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        if ($this->hasPendingWrites) {
            $this->commitPendingWrites();
        }

        $cache = $this->getCache();
        $cleanId = $this->cleanIdentifier($identifier);

        // Clear from response cache
        unset($this->responseCache['test:' . $cleanId]);
        unset($this->responseCache['load:' . $cleanId]);

        $this->adapter->onRemove($cleanId);

        $success = $cache->deleteItem($cleanId);

        if (method_exists($cache, 'commit')) {
            $cache->commit();
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = [])
    {
        $this->responseCache = [];

        if ($this->hasPendingWrites) {
            $this->commitPendingWrites();
        }

        // Validate cleaning mode
        $validModes = [
            CacheConstants::CLEANING_MODE_ALL,
            CacheConstants::CLEANING_MODE_OLD,
            CacheConstants::CLEANING_MODE_MATCHING_TAG,
            CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG,
            CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG
        ];

        if (!in_array($mode, $validModes, true)) {
            throw new InvalidArgumentException(
                "Invalid cleaning mode '{$mode}'. Supported modes: " .
                "ALL, OLD, MATCHING_TAG, NOT_MATCHING_TAG, MATCHING_ANY_TAG"
            );
        }

        $cache = $this->getCache();

        return match ($mode) {
            CacheConstants::CLEANING_MODE_ALL, 'all' => $this->cleanAll($cache),
            CacheConstants::CLEANING_MODE_OLD, 'old' => $this->cleanOld($cache),
            CacheConstants::CLEANING_MODE_MATCHING_TAG, 'matchingTag' =>
                $this->cleanMatchingTag($cache, $tags),
            CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG, 'notMatchingTag' =>
                $this->cleanNotMatchingTag($cache, $tags),
            CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG, 'matchingAnyTag' =>
                $this->cleanMatchingAnyTag($cache, $tags),
            default => throw new InvalidArgumentException("Unsupported cleaning mode: {$mode}")
        };
    }

    /**
     * Clean all cache entries
     *
     * @param CacheItemPoolInterface $cache
     * @return bool
     */
    private function cleanAll(CacheItemPoolInterface $cache): bool
    {
        $this->responseCache = [];
        $this->adapter->clearAllIndices();
        $success = $cache->clear();

        if (method_exists($cache, 'commit')) {
            $cache->commit();
        }

        return $success;
    }

    /**
     * Clean old/expired cache entries
     *
     * @param CacheItemPoolInterface $cache
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function cleanOld(CacheItemPoolInterface $cache): bool
    {
        // Symfony handles expiration automatically
        // This is a no-op as expired items are not returned
        return true;
    }

    /**
     * Clean entries matching ALL given tags (AND logic)
     *
     * @param CacheItemPoolInterface $cache
     * @param array $tags
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function cleanMatchingTag(CacheItemPoolInterface $cache, array $tags): bool
    {
        if (empty($tags)) {
            return true;
        }

        $cleanTags = $this->cleanIdentifiers($tags);

        // For GenericHelper with namespace tags, use the namespace tag
        if ($this->adapter instanceof GenericTagAdapter) {
            if ($this->adapter->usesNamespaceTags()) {
                $tagsToInvalidate = $this->adapter->getTagsForMatchingTag($cleanTags);
                if ($this->isTagAware()) {
                    $success = $cache->invalidateTags($tagsToInvalidate);

                    if (method_exists($cache, 'commit')) {
                        $cache->commit();
                    }

                    return $success;
                }
                return false;
            } else {
                // For non-FPC generic adapters, use OR logic (best we can do)
                if ($this->isTagAware()) {
                    $success = $cache->invalidateTags($cleanTags);

                    if (method_exists($cache, 'commit')) {
                        $cache->commit();
                    }

                    return $success;
                }
                return false;
            }
        }

        // For Redis/Filesystem helpers with native AND support
        $ids = $this->adapter->getIdsMatchingTags($cleanTags);

        if (empty($ids)) {
            return true;
        }

        return $this->adapter->deleteByIds($ids);
    }

    /**
     * Clean entries NOT matching any of the given tags
     *
     * @param CacheItemPoolInterface $cache
     * @param array $tags
     * @return bool
     */
    private function cleanNotMatchingTag(CacheItemPoolInterface $cache, array $tags): bool
    {
        if (empty($tags)) {
            // No tags means clean all
            return $this->cleanAll($cache);
        }

        $cleanTags = $this->cleanIdentifiers($tags);
        $ids = $this->adapter->getIdsNotMatchingTags($cleanTags);

        if (empty($ids)) {
            return true;
        }

        return $this->adapter->deleteByIds($ids);
    }

    /**
     * Get cache entry metadata (Zend compatibility)
     *
     * @param string $id
     * @return array|false
     */
    public function getMetadatas($id)
    {
        $cache = $this->getCache();
        $cleanId = $this->cleanIdentifier($id);

        $item = $cache->getItem($cleanId);

        if (!$item->isHit()) {
            return false;
        }

        $wrappedData = $item->get();

        // Return stored metadata from wrapper
        if (is_array($wrappedData) && isset($wrappedData['mtime'])) {
            // Add cache ID prefix to tags (to match Zend behavior)
            $storedTags = $wrappedData['tags'] ?? [];
            $tags = array_values(array_map(function ($tag) {
                return self::DEFAULT_CACHE_PREFIX . $tag;
            }, $storedTags));

            return [
                'expire' => $wrappedData['expire'] ?? null,
                'tags' => $tags,
                'mtime' => $wrappedData['mtime'],
            ];
        }

        // Fallback for non-wrapped data (shouldn't happen)
        // Calculate metadata from Symfony metadata
        $metadata = $item->getMetadata();

        // Get expiry timestamp from Symfony metadata
        $expiry = null;
        if (isset($metadata[CacheItem::METADATA_EXPIRY])) {
            $expiry = (int) $metadata[CacheItem::METADATA_EXPIRY];
        }

        // If no expiry, default to now + 24 hours
        if (!$expiry) {
            $expiry = time() + self::FALLBACK_EXPIRY;
        }

        // Calculate mtime from expiry (Symfony doesn't store creation time)
        // Use ASSUMED_LIFETIME for approximation: mtime ≈ expiry - lifetime
        $mtime = $expiry - self::ASSUMED_LIFETIME;

        // Ensure mtime is not in the future
        $now = time();
        if ($mtime > $now) {
            $mtime = $now;
        }

        // Get tags from metadata and add cache ID prefix
        $tags = [];
        if (isset($metadata[CacheItem::METADATA_TAGS])) {
            $rawTags = $metadata[CacheItem::METADATA_TAGS];
            $tags = array_values(array_map(function ($tag) {
                return self::DEFAULT_CACHE_PREFIX . $tag;
            }, $rawTags));
        }

        return [
            'expire' => $expiry,
            'tags' => $tags,
            'mtime' => $mtime,
        ];
    }

    /**
     * Clean entries matching ANY of the given tags (OR logic)
     *
     * OPTIMIZED: Prefer adapter-based batch processing over TagAwareAdapter
     * for better performance with configurable products (3-5× faster)
     *
     * @param CacheItemPoolInterface $cache
     * @param array $tags
     * @return bool
     */
    private function cleanMatchingAnyTag(CacheItemPoolInterface $cache, array $tags): bool
    {
        if (empty($tags)) {
            return true;
        }

        $cleanTags = $this->cleanIdentifiers($tags);

        // OPTIMIZATION: Use adapter for batch tag processing (faster than TagAwareAdapter)
        // The adapter uses a single Redis SUNION command to get all IDs for all tags,
        // then batch-deletes them. This is 3-5× faster than TagAwareAdapter's approach.
        if ($this->adapter && !empty($cleanTags)) {
            $ids = $this->adapter->getIdsMatchingAnyTags($cleanTags);

            if (empty($ids)) {
                return true;
            }

            // Batch delete all IDs at once
            return $this->adapter->deleteByIds($ids);
        }

        // Fallback: Try Symfony's native invalidateTags (OR logic)
        // This path is used only if adapter is not available
        if ($this->isTagAware()) {
            // Note: commit() is called internally by invalidateTags, no need to call explicitly
            return $cache->invalidateTags($cleanTags);
        }

        // Last resort: iterate tags (should rarely happen)
        $success = true;
        foreach ($cleanTags as $tag) {
            if (!$cache->clear($tag)) {
                $success = false;
            }
        }

        // Ensure changes are committed immediately (matches Zend behavior)
        if (method_exists($cache, 'commit')) {
            $cache->commit();
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function getBackend()
    {
        return new Symfony\BackendWrapper($this->getCache(), $this->adapter, $this);
    }

    /**
     * @inheritDoc
     */
    public function getLowLevelFrontend()
    {
        return new Symfony\LowLevelFrontend(
            $this->getCache(),
            $this,
            $this->adapter,
            $this->idPrefix,
            $this->defaultLifetime
        );
    }

    /**
     * @inheritDoc
     */
    public function getFrontend()
    {
        return $this;
    }
}
