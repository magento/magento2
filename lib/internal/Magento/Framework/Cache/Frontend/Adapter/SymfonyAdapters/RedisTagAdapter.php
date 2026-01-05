<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters;

use Magento\Framework\Cache\Frontend\Adapter\OptimizedPredisClient;
use Predis\Client as PredisClient;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

/**
 * Redis-specific tag adapter
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedisTagAdapter implements TagAdapterInterface
{
    private const TAG_INDEX_PREFIX = 'cache:tags:';
    private const ALL_IDS_SET = 'cache:all_ids';

    /**
     * SUNION chunk size
     * On large data sets SUNION slows down considerably when used with too many arguments
     * @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 92
     */
    private const SUNION_CHUNK_SIZE = 500;

    /**
     * Maximum number of IDs to be removed at a time - matches Zend's $_removeChunkSize
     * @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 99
     */
    private const REMOVE_CHUNK_SIZE = 10000;

    /**
     * Lua's unpack() limit - matches Zend's $_luaMaxCStack
     * @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 121
     */
    private const LUA_MAX_CSTACK = 5000;

    /**
     * Lua script for cleaning cache entries matching ANY tags (OR logic)
     *
     * This matches Zend's LUA_CLEAN_SH1 implementation exactly
     * (vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 780-798)
     *
     * Performance: Single atomic Redis operation, ~10-15% faster than PHP implementation
     */
    private const LUA_CLEAN_MATCHING_ANY_TAGS = <<<'LUA'
-- KEYS: array of tags to match (e.g., ["product", "category", "config"])
-- ARGV[1]: tag prefix (e.g., "cache:tags:")
-- ARGV[2]: namespace prefix (e.g., "69d_")
-- ARGV[3]: chunk size for SUNION operations

local tag_prefix = ARGV[1]
local namespace = ARGV[2]
local chunk_size = tonumber(ARGV[3]) or 100

-- Build prefixed tag keys
local prefixed_tags = {}
for i, tag in ipairs(KEYS) do
    prefixed_tags[i] = tag_prefix .. namespace .. tag
end

-- Get IDs matching ANY of the tags using SUNION
local ids_to_delete = redis.call('SUNION', unpack(prefixed_tags))

if #ids_to_delete == 0 then
    return 0
end

-- Delete cache items and remove from indices
local deleted = 0
for _, id in ipairs(ids_to_delete) do
    -- Delete the actual cache item
    local cache_key = namespace .. id
    redis.call('DEL', cache_key)
    deleted = deleted + 1
end

return deleted
LUA;

    /**
     * Lua script for cleaning cache entries matching ANY tags within a scope (OR + AND logic)
     *
     * Logic: (tag1 OR tag2 OR ...) AND scopeTag
     *
     * Performance: Single atomic Redis operation with scope filtering
     */
    private const LUA_CLEAN_MATCHING_ANY_TAGS_WITH_SCOPE = <<<'LUA'
-- KEYS: array of tags to match (e.g., ["product", "category"])
-- ARGV[1]: tag prefix (e.g., "cache:tags:")
-- ARGV[2]: namespace prefix (e.g., "69d_")
-- ARGV[3]: scope tag (e.g., "FPC")

local tag_prefix = ARGV[1]
local namespace = ARGV[2]
local scope_tag = ARGV[3]

-- Build prefixed tag keys
local prefixed_tags = {}
for i, tag in ipairs(KEYS) do
    prefixed_tags[i] = tag_prefix .. namespace .. tag
end

-- Step 1: Get IDs matching ANY of the tags using SUNION
local any_ids = redis.call('SUNION', unpack(prefixed_tags))

if #any_ids == 0 then
    return 0
end

-- Step 2: Get IDs matching the scope tag
local scope_key = tag_prefix .. namespace .. scope_tag
local scope_ids = redis.call('SMEMBERS', scope_key)

if #scope_ids == 0 then
    return 0
end

-- Step 3: Intersect in Lua (find IDs in both sets)
local scope_set = {}
for _, id in ipairs(scope_ids) do
    scope_set[id] = true
end

local filtered_ids = {}
for _, id in ipairs(any_ids) do
    if scope_set[id] then
        table.insert(filtered_ids, id)
    end
end

if #filtered_ids == 0 then
    return 0
end

-- Step 4: Delete filtered IDs
local deleted = 0
for _, id in ipairs(filtered_ids) do
    local cache_key = namespace .. id
    redis.call('DEL', cache_key)
    deleted = deleted + 1
end

return deleted
LUA;

    /**
     * @var \Redis|\RedisCluster|PredisClient|OptimizedPredisClient
     */
    private \Redis|\RedisCluster|PredisClient|OptimizedPredisClient $redis;

    /**
     * @var string
     */
    private string $namespace;

    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cachePool;

    /**
     * @var RedisLuaHelper|null
     */
    private ?RedisLuaHelper $luaHelper = null;

    /**
     * @var bool
     */
    private bool $useLua;

    /**
     * @var bool
     */
    private bool $useLuaOnGc;

    /**
     * @param CacheItemPoolInterface $cachePool
     * @param string $namespace Cache namespace/prefix
     * @param bool $useLua Enable Lua scripts for cache operations
     * @param bool $useLuaOnGc Enable Lua scripts for garbage collection
     */
    public function __construct(
        CacheItemPoolInterface $cachePool,
        string $namespace = '',
        bool $useLua = false,
        bool $useLuaOnGc = false
    ) {
        $this->cachePool = $cachePool;
        $this->namespace = $namespace;
        $this->redis = $this->extractRedisClient($cachePool);

        if ($this->isPredisClient()) {
            $this->useLua = false;
            $this->useLuaOnGc = false;
        } else {
            $this->useLua = $useLua;
            $this->useLuaOnGc = $useLuaOnGc;
        }

        if (($this->useLua || $this->useLuaOnGc) && !$this->isPredisClient()) {
            $this->luaHelper = new RedisLuaHelper($this->redis, true);
        }
    }

    /**
     * Extract Redis client from Symfony cache adapter
     *
     * @param CacheItemPoolInterface $cachePool
     * @return \Redis|\RedisCluster|PredisClient|OptimizedPredisClient
     * @throws \RuntimeException If Redis client cannot be extracted
     */
    private function extractRedisClient(
        CacheItemPoolInterface $cachePool
    ): \Redis|\RedisCluster|PredisClient|OptimizedPredisClient {
        $adapter = $cachePool;
        if ($adapter instanceof TagAwareAdapter) {
            $reflection = new \ReflectionClass($adapter);
            $poolProperty = $reflection->getProperty('pool');
            $poolProperty->setAccessible(true);
            $adapter = $poolProperty->getValue($adapter);
        }

        // Get Redis client from RedisAdapter
        if ($adapter instanceof RedisAdapter) {
            $reflection = new \ReflectionClass($adapter);
            $redisProperty = $reflection->getProperty('redis');
            $redisProperty->setAccessible(true);
            $redis = $redisProperty->getValue($adapter);

            if ($redis instanceof \Redis || $redis instanceof \RedisCluster ||
                $redis instanceof PredisClient || $redis instanceof OptimizedPredisClient) {
                return $redis;
            }
        }

        throw new \RuntimeException('Could not extract Redis client from cache adapter');
    }

    /**
     * Get prefixed tag name for Redis SET key
     *
     * @param string $tag
     * @return string
     */
    private function getTagKey(string $tag): string
    {
        return self::TAG_INDEX_PREFIX . $this->namespace . $tag;
    }

    /**
     * Check if using Predis client (vs phpredis extension)
     *
     * @return bool
     */
    private function isPredisClient(): bool
    {
        return $this->redis instanceof PredisClient || $this->redis instanceof OptimizedPredisClient;
    }

    /**
     * Create Redis pipeline compatible with both phpredis and Predis
     *
     * @return \Redis|object Predis pipeline object
     */
    private function createPipeline()
    {
        if ($this->isPredisClient()) {
            return $this->redis->pipeline();
        }

        return $this->redis->multi(\Redis::PIPELINE);
    }

    /**
     * Execute Redis pipeline compatible with both phpredis and Predis
     *
     * @param \Redis|object $pipeline
     * @return mixed
     */
    private function executePipeline($pipeline)
    {
        if ($pipeline instanceof PredisClient || method_exists($pipeline, 'execute')) {
            // Predis pipeline
            return $pipeline->execute();
        }

        // phpredis pipeline
        return $pipeline->exec();
    }

    /**
     * @inheritDoc
     *
     * Uses Redis SINTER for efficient set intersection (true AND logic)
     */
    public function getIdsMatchingTags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        // Build tag keys for Redis SINTER
        $tagKeys = array_map([$this, 'getTagKey'], $tags);

        // Redis SINTER returns IDs present in ALL sets
        $ids = $this->redis->sinter($tagKeys);

        return is_array($ids) ? $ids : [];
    }

    /**
     * @inheritDoc
     *
     * Uses Redis SUNION for efficient set union (OR logic)
     *
     * OPTIMIZED: Single tag uses SMEMBERS (faster), multiple tags use SUNION
     * Redis SUNION already returns unique values, no need for array_unique()
     */
    public function getIdsMatchingAnyTags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        // OPTIMIZATION: For single tag, use SMEMBERS directly (faster than SUNION)
        if (count($tags) === 1) {
            $ids = $this->redis->sMembers($this->getTagKey($tags[0]));
            return is_array($ids) ? $ids : [];
        }

        // Matches Zend's implementation to prevent Redis slowdowns
        // @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 777-778
        if (count($tags) > self::SUNION_CHUNK_SIZE) {
            $allIds = [];
            $chunks = array_chunk($tags, self::SUNION_CHUNK_SIZE);

            foreach ($chunks as $chunk) {
                $tagKeys = array_map([$this, 'getTagKey'], $chunk);
                $chunkIds = $this->redis->sUnion($tagKeys);
                $chunkIds = is_array($chunkIds) ? $chunkIds : [];

                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $allIds = array_merge($allIds, $chunkIds);
            }

            return array_unique($allIds);
        }

        $tagKeys = array_map([$this, 'getTagKey'], $tags);
        $ids = $this->redis->sUnion($tagKeys);

        return is_array($ids) ? $ids : [];
    }

    /**
     * @inheritDoc
     *
     * Gets all IDs and removes those matching any of the given tags
     */
    public function getIdsNotMatchingTags(array $tags): array
    {
        if (empty($tags)) {
            // Return all IDs if no tags specified
            $allIds = $this->redis->smembers(self::ALL_IDS_SET);
            return is_array($allIds) ? $allIds : [];
        }

        $tagKeys = array_map([$this, 'getTagKey'], $tags);

        // Prepend the all_ids set as first argument
        array_unshift($tagKeys, self::ALL_IDS_SET);

        // Call SDIFF: returns IDs in ALL_IDS_SET but NOT in any tag sets
        $result = call_user_func_array([$this->redis, 'sdiff'], $tagKeys);

        return is_array($result) ? $result : [];
    }

    /**
     * @inheritDoc
     *
     * OPTIMIZED: Uses Redis pipeline for large batches
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function deleteByIds(array $ids): bool
    {
        if (empty($ids)) {
            return true;
        }

        // Matches Zend's implementation to prevent Redis blocking and memory issues
        // @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 809-825
        if (count($ids) > self::REMOVE_CHUNK_SIZE) {
            $chunks = array_chunk($ids, self::REMOVE_CHUNK_SIZE);
            $success = true;

            foreach ($chunks as $chunk) {
                // Delete cache items for this chunk
                if (!$this->cachePool->deleteItems($chunk)) {
                    $success = false;
                }

                // Remove IDs from all_ids set for this chunk
                $pipeline = $this->createPipeline();
                foreach ($chunk as $id) {
                    $pipeline->srem(self::ALL_IDS_SET, $id);
                }
                $this->executePipeline($pipeline);

                // Commit each chunk separately (important for large operations)
                if (method_exists($this->cachePool, 'commit')) {
                    $this->cachePool->commit();
                }
            }

            return $success;
        }

        $success = $this->cachePool->deleteItems($ids);

        if (count($ids) > 10) {
            $pipeline = $this->createPipeline();

            // Remove each ID from all_ids set in pipeline
            foreach ($ids as $id) {
                $pipeline->srem(self::ALL_IDS_SET, $id);
            }

            $this->executePipeline($pipeline);
        } else {
            // For small batches, use single command (slightly faster)
            array_unshift($ids, self::ALL_IDS_SET);
            call_user_func_array([$this->redis, 'sRem'], $ids);
        }

        // Ensure changes are committed immediately (important for MFTF and tests)
        if (method_exists($this->cachePool, 'commit')) {
            $this->cachePool->commit();
        }

        return $success;
    }

    /**
     * Clean cache entries matching ANY of the given tags (OR logic)
     *
     * @param array $tags Tags to match (OR logic)
     * @return bool
     */
    public function cleanMatchingAnyTags(array $tags): bool
    {
        if (empty($tags)) {
            return true;
        }

        // Lua path (if enabled) - matches Zend's Lua script (line 776-801)
        if ($this->useLua && $this->luaHelper && $this->luaHelper->isEnabled()) {
            try {
                $deleted = $this->cleanMatchingAnyTagsLua($tags);

                // Ensure changes are committed
                if (method_exists($this->cachePool, 'commit')) {
                    $this->cachePool->commit();
                }

                return $deleted >= 0; // Lua returns number of items deleted
            // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
            } catch (\Exception $e) {
                // Intentional: Fall through to PHP implementation on Lua failure
            }
            // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
        }

        // PHP path (fallback) - matches Zend's PHP path (line 804-812)
        $ids = $this->getIdsMatchingAnyTags($tags);

        if (empty($ids)) {
            return true;
        }

        // Batch delete - exactly like Zend's _removeByIds (line 751-768)
        $success = $this->deleteByIds($ids);

        // Ensure changes are committed to underlying pool
        if (method_exists($this->cachePool, 'commit')) {
            $this->cachePool->commit();
        }

        return $success;
    }

    /**
     * Clean cache entries matching ANY tags within a scope (OR + AND logic)
     *
     * @param array $tags Tags to match (OR logic)
     * @param string $scopeTag Scope tag to filter by (AND logic)
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function cleanMatchingAnyTagsWithScope(array $tags, string $scopeTag): bool
    {
        if (empty($tags)) {
            return true;
        }

        // Lua path (if enabled) - atomic operation with scope filtering
        if ($this->useLua && $this->luaHelper && $this->luaHelper->isEnabled()) {
            try {
                $deleted = $this->cleanMatchingAnyTagsWithScopeLua($tags, $scopeTag);

                // Ensure changes are committed
                if (method_exists($this->cachePool, 'commit')) {
                    $this->cachePool->commit();
                }

                return $deleted >= 0; // Lua returns number of items deleted
            // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
            } catch (\Exception $e) {
                // Intentional: Fall through to PHP implementation on Lua failure
            }
            // phpcs:enable Magento2.CodeAnalysis.EmptyBlock
        }

        // Step 1: Get IDs matching ANY of the tags using SUNION (OR logic)
        $anyIds = $this->getIdsMatchingAnyTags($tags);

        if (empty($anyIds)) {
            return true;
        }

        // Step 2: Get IDs matching the scope tag using SMEMBERS
        $scopeIds = $this->redis->sMembers($this->getTagKey($scopeTag));

        if (!is_array($scopeIds) || empty($scopeIds)) {
            return true;
        }

        // Step 3: Intersect to get IDs that have (tag1 OR tag2 OR ...) AND scopeTag
        $filteredIds = array_intersect($anyIds, $scopeIds);

        if (empty($filteredIds)) {
            return true;
        }

        // Step 4: Batch delete filtered IDs
        $success = $this->deleteByIds($filteredIds);

        // Step 5: Ensure changes are committed to underlying pool
        if (method_exists($this->cachePool, 'commit')) {
            $this->cachePool->commit();
        }

        return $success;
    }

    /**
     * @inheritDoc
     *
     * Maintains tag-to-ID indices in Redis SETs
     * OPTIMIZED: Uses Redis pipeline for batch operations
     */
    public function onSave(string $id, array $tags): void
    {
        if (empty($tags)) {
            return;
        }

        $pipeline = $this->createPipeline();

        // Add ID to all_ids set
        $pipeline->sadd(self::ALL_IDS_SET, $id);

        // Forward index: Add ID to each tag's SET
        foreach ($tags as $tag) {
            $tagKey = $this->getTagKey($tag);
            $pipeline->sadd($tagKey, $id);
        }

        // Reverse index: Store tags for this ID (for cleanup on delete)
        $idTagsKey = 'cache:id_tags:' . $this->namespace . $id;
        $pipeline->del($idTagsKey);  // Clear old tags first
        foreach ($tags as $tag) {
            $pipeline->sadd($idTagsKey, $tag);
        }

        // Execute all operations in one go
        $this->executePipeline($pipeline);
    }

    /**
     * @inheritDoc
     *
     * Removes ID from all tag indices
     * OPTIMIZED: Uses Redis pipeline for batch operations
     */
    public function onRemove(string $id): void
    {
        // Find which tags this ID was associated with store a reverse index: cache:id:tags => SET{tag1, tag2}
        $idTagsKey = 'cache:id_tags:' . $this->namespace . $id;
        $tags = $this->redis->smembers($idTagsKey);

        if (!is_array($tags) || empty($tags)) {
            // No tags, just remove from all_ids
            $this->redis->srem(self::ALL_IDS_SET, $id);
            return;
        }

        // OPTIMIZATION: Use Redis pipeline for all remove operations, reduces network round trips from N+2 to 1
        $pipeline = $this->createPipeline();

        // Remove from all_ids set
        $pipeline->srem(self::ALL_IDS_SET, $id);

        // Remove ID from each tag's SET in pipeline
        foreach ($tags as $tag) {
            $tagKey = $this->getTagKey($tag);
            $pipeline->srem($tagKey, $id);
        }

        // Delete the reverse index
        $pipeline->del($idTagsKey);

        // Execute all operations in one go
        $this->executePipeline($pipeline);
    }

    /**
     * @inheritDoc
     */
    public function clearAllIndices(): void
    {
        // Use Lua script if enabled for atomic, efficient clearing
        if ($this->useLua && $this->luaHelper) {
            $this->luaHelper->clearAllIndices($this->namespace);
            // Lua script handles everything atomically
            return;
        }

        // Fallback: PHP-based clearing (original implementation)
        // Get all tag keys
        $pattern = self::TAG_INDEX_PREFIX . $this->namespace . '*';
        $tagKeys = $this->redis->keys($pattern);

        if (is_array($tagKeys) && !empty($tagKeys)) {
            // PHP 8+ compatibility: use call_user_func_array to avoid spread operator issues
            call_user_func_array([$this->redis, 'del'], $tagKeys);
        }

        // Clear all_ids set
        $this->redis->del(self::ALL_IDS_SET);

        // Clear reverse index keys
        $reversePattern = 'cache:id_tags:' . $this->namespace . '*';
        $reverseKeys = $this->redis->keys($reversePattern);
        if (is_array($reverseKeys) && !empty($reverseKeys)) {
            // PHP 8+ compatibility: use call_user_func_array to avoid spread operator issues
            call_user_func_array([$this->redis, 'del'], $reverseKeys);
        }
    }

    /**
     * Store reverse index for efficient onRemove, This should be called after onSave
     *
     * @param string $id
     * @param array $tags
     * @return void
     */
    public function storeReverseIndex(string $id, array $tags): void
    {
        if (empty($tags)) {
            return;
        }

        $idTagsKey = 'cache:id_tags:' . $this->namespace . $id;

        // OPTIMIZATION: Use Redis pipeline for all operations
        // Reduces network round trips from N+1 to 1
        $pipeline = $this->createPipeline();

        // Clear existing reverse index
        $pipeline->del($idTagsKey);

        // Add all tags to reverse index in pipeline
        foreach ($tags as $tag) {
            $pipeline->sadd($idTagsKey, $tag);
        }

        // Execute all operations in one go
        $this->executePipeline($pipeline);
    }

    /**
     * Run garbage collection to clean expired items
     *
     * @param int $batchSize Number of keys to process per iteration
     * @return int Number of items cleaned
     */
    public function garbageCollect(int $batchSize = 1000): int
    {
        // Garbage collection specifically checks use_lua_on_gc flag
        if (!$this->useLuaOnGc || !$this->luaHelper) {
            return 0;
        }

        $result = $this->luaHelper->garbageCollect(
            $this->namespace . '*',
            self::TAG_INDEX_PREFIX . $this->namespace,
            $batchSize
        );

        return $result[0]; // Return deleted count (first element)
    }

    /**
     * Check if Lua scripts are enabled and available
     *
     * @return bool
     */
    public function isLuaEnabled(): bool
    {
        return ($this->useLua || $this->useLuaOnGc)
            && $this->luaHelper !== null
            && $this->luaHelper->isEnabled();
    }

    /**
     * Clean expired items for specific tag using Lua
     *
     * Only deletes items that have expired (TTL = -2)
     * More efficient than fetching all IDs and checking client-side
     * Uses use_lua flag (general cache operations)
     *
     * @param string $tag Tag to clean
     * @return int Number of items deleted
     */
    public function cleanExpiredByTag(string $tag): int
    {
        // Tag operations check use_lua flag
        if (!$this->useLua || !$this->luaHelper) {
            return 0;
        }

        $tagKey = $this->getTagKey($tag);

        return $this->luaHelper->cleanByTagConditional(
            $tagKey,
            $this->namespace,
            'expired'
        );
    }

    /**
     * Clean cache entries matching ANY tags using Lua script
     *
     * @param array $tags Tags to match (OR logic)
     * @return int Number of items deleted (-1 on error)
     */
    private function cleanMatchingAnyTagsLua(array $tags): int
    {
        if (empty($tags)) {
            return 0;
        }

        try {
            // Load and execute Lua script
            $sha = $this->loadLuaScript(self::LUA_CLEAN_MATCHING_ANY_TAGS);

            // KEYS: array of tags
            // ARGV: [tag_prefix, namespace, chunk_size]
            $result = $this->redis->evalSha(
                $sha,
                $tags,  // KEYS
                count($tags),  // Number of KEYS
                self::TAG_INDEX_PREFIX,  // ARGV[1]
                $this->namespace,  // ARGV[2]
                100  // ARGV[3] - chunk size
            );

            return (int)$result;
        } catch (\RedisException $e) {
            // Fallback: try executing script directly
            try {
                $result = $this->redis->eval(
                    self::LUA_CLEAN_MATCHING_ANY_TAGS,
                    $tags,
                    count($tags),
                    self::TAG_INDEX_PREFIX,
                    $this->namespace,
                    100
                );
                return (int)$result;
            } catch (\RedisException $e) {
                // Return -1 to signal error (will fall back to PHP)
                return -1;
            }
        }
    }

    /**
     * Clean cache entries matching ANY tags within scope using Lua script
     *
     * @param array $tags Tags to match (OR logic)
     * @param string $scopeTag Scope tag to filter by (AND logic)
     * @return int Number of items deleted (-1 on error)
     */
    private function cleanMatchingAnyTagsWithScopeLua(array $tags, string $scopeTag): int
    {
        if (empty($tags)) {
            return 0;
        }

        try {
            // Load and execute Lua script
            $sha = $this->loadLuaScript(self::LUA_CLEAN_MATCHING_ANY_TAGS_WITH_SCOPE);

            // KEYS: array of tags
            // ARGV: [tag_prefix, namespace, scope_tag]
            $result = $this->redis->evalSha(
                $sha,
                $tags,  // KEYS
                count($tags),  // Number of KEYS
                self::TAG_INDEX_PREFIX,  // ARGV[1]
                $this->namespace,  // ARGV[2]
                $scopeTag  // ARGV[3]
            );

            return (int)$result;
        } catch (\RedisException $e) {
            // Fallback: try executing script directly
            try {
                $result = $this->redis->eval(
                    self::LUA_CLEAN_MATCHING_ANY_TAGS_WITH_SCOPE,
                    $tags,
                    count($tags),
                    self::TAG_INDEX_PREFIX,
                    $this->namespace,
                    $scopeTag
                );
                return (int)$result;
            } catch (\RedisException $e) {
                // Return -1 to signal error (will fall back to PHP)
                return -1;
            }
        }
    }

    /**
     * Load Lua script and return SHA1
     *
     * @param string $script Lua script content
     * @return string SHA1 of the script
     * @throws \RedisException
     */
    private function loadLuaScript(string $script): string
    {
        try {
            return $this->redis->script('load', $script);
        } catch (\RedisException $e) {
            throw new \RedisException('Failed to load Lua script: ' . $e->getMessage(), 0, $e);
        }
    }
}
