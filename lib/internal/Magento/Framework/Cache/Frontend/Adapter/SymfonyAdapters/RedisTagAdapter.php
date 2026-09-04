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
    private const REVERSE_INDEX_PREFIX = 'cache:id_tags:';

    /**
     * Key prefix for the stale-cache regeneration lock (owned by SymfonyL2Cache).
     * Kept here because this adapter owns the raw Redis client and its type handling.
     */
    private const REGEN_LOCK_PREFIX = 'cache:regen_lock:';

    /**
     * Atomically prune the tag index for a batch of ids using their reverse index.
     *
     * For each id: read its reverse index (id -> tags), SREM the id from every tag SET, DEL the
     * reverse index and SREM the id from all_ids. Running inside EVAL makes the whole
     * read-modify-write atomic, so a concurrent onSave for the same id cannot be half-clobbered.
     *
     * Existence guard (finding #4): an id is pruned ONLY when its data key is gone. If a concurrent
     * save has just (re)written the data, the index is left intact so the index never contradicts
     * the data (no data-present / index-missing orphan). The guard fails safe: if the data-key
     * prefix is wrong the EXISTS is 0 and pruning proceeds exactly as before.
     *
     * ARGV[1] = tag key prefix, ARGV[2] = namespace, ARGV[3] = data-key prefix (namespace + ':'),
     * ARGV[4..] = ids to prune.
     */
    private const LUA_PRUNE_INDEX = <<<'LUA'
local tag_prefix = ARGV[1]
local namespace = ARGV[2]
local data_prefix = ARGV[3]
local rev_prefix = 'cache:id_tags:' .. namespace
local pruned = 0
for i = 4, #ARGV do
    local id = ARGV[i]
    if redis.call('EXISTS', data_prefix .. id) == 0 then
        local rev = rev_prefix .. id
        local tags_of_id = redis.call('SMEMBERS', rev)
        for _, t in ipairs(tags_of_id) do
            redis.call('SREM', tag_prefix .. namespace .. t, id)
        end
        redis.call('DEL', rev)
        redis.call('SREM', 'cache:all_ids', id)
        pruned = pruned + 1
    end
end
return pruned
LUA;

    /**
     * Atomically (re)build the tag index for one id, but ONLY while its data key exists.
     *
     * The pair (LUA_ONSAVE guarded on data-present, LUA_PRUNE_INDEX guarded on data-absent) makes
     * the two index operations each conditional on the current data state, so the tag index can
     * never end up contradicting the data under a concurrent save/delete on the same id
     * (finding #4). The data write is committed before onSave runs, so under normal (non-racing)
     * saves EXISTS is 1 and the index is written exactly as the old pipeline did.
     *
     * ARGV[1] = data key (namespace + ':' + id), ARGV[2] = id, ARGV[3] = all_ids key,
     * ARGV[4] = tag key prefix, ARGV[5] = namespace, ARGV[6] = reverse index key,
     * ARGV[7..] = tag names.
     */
    private const LUA_ONSAVE = <<<'LUA'
if redis.call('EXISTS', ARGV[1]) == 0 then
    return 0
end
local id = ARGV[2]
local all_ids = ARGV[3]
local tag_prefix = ARGV[4]
local namespace = ARGV[5]
local reverse_key = ARGV[6]
-- Retag cleanup: drop the id from the forward SET of any tag it no longer carries. Without this a
-- re-save with a different tag set (e.g. [A,B] then [C]) would leave the old A/B memberships
-- dangling because the reverse index is about to be replaced. Mirrors the legacy
-- Cm_Cache_Backend_Redis save() which array_diffs the previous tags and SREMs them.
local keep = {}
for i = 7, #ARGV do
    keep[ARGV[i]] = true
end
local old_tags = redis.call('SMEMBERS', reverse_key)
for j = 1, #old_tags do
    if not keep[old_tags[j]] then
        redis.call('SREM', tag_prefix .. namespace .. old_tags[j], id)
    end
end
redis.call('SADD', all_ids, id)
redis.call('DEL', reverse_key)
for i = 7, #ARGV do
    local tag = ARGV[i]
    redis.call('SADD', tag_prefix .. namespace .. tag, id)
    redis.call('SADD', reverse_key, tag)
end
return 1
LUA;

    /**
     * SUNION chunk size
     * On large data sets SUNION slows down considerably when used with too many arguments
     * @see vendor/colinmollenhour/cache-backend-redis/Cm/Cache/Backend/Redis.php line 92
     */
    private const SUNION_CHUNK_SIZE = 500;

    /**
     * Maximum set size for which a single SINTER call is safe.
     * SINTER is O(N*M) where N = smallest set size. If the smallest set exceeds
     * this threshold, SINTER blocks Valkey long enough to saturate PHP-FPM workers.
     * Above the threshold, SMEMBERS + PHP array_intersect is used instead.
     */
    private const SINTER_SAFE_SIZE = 500;

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

-- Delete cache items and remove from indices (forward tag SETs, reverse index, all_ids)
local deleted = 0
local rev_prefix = 'cache:id_tags:' .. namespace
for _, id in ipairs(ids_to_delete) do
    -- Remove the id from every tag SET it belongs to, via its reverse index
    local rev = rev_prefix .. id
    local tags_of_id = redis.call('SMEMBERS', rev)
    for _, t in ipairs(tags_of_id) do
        redis.call('SREM', tag_prefix .. namespace .. t, id)
    end
    redis.call('DEL', rev)
    redis.call('SREM', 'cache:all_ids', id)

    -- Delete the actual cache item (data key is "<namespace>:<id>", note the ':' separator)
    local cache_key = namespace .. ':' .. id
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

-- Step 4: Delete filtered IDs and remove from indices (tag SETs, reverse index, all_ids)
local deleted = 0
local rev_prefix = 'cache:id_tags:' .. namespace
for _, id in ipairs(filtered_ids) do
    local rev = rev_prefix .. id
    local tags_of_id = redis.call('SMEMBERS', rev)
    for _, t in ipairs(tags_of_id) do
        redis.call('SREM', tag_prefix .. namespace .. t, id)
    end
    redis.call('DEL', rev)
    redis.call('SREM', 'cache:all_ids', id)

    -- Delete the actual cache item (data key is "<namespace>:<id>", note the ':' separator)
    local cache_key = namespace .. ':' .. id
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

        // Lua is honored on both drivers: RedisLuaHelper normalizes the phpredis vs Predis EVAL
        // signatures, so use_lua=1 gives Predis the same atomic tag-index prune as phpredis instead
        // of silently degrading to the non-atomic pipeline path.
        $this->useLua = $useLua;
        $this->useLuaOnGc = $useLuaOnGc;

        if ($this->useLua || $this->useLuaOnGc) {
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
            $adapter = $poolProperty->getValue($adapter);
        }

        // Get Redis client from RedisAdapter
        if ($adapter instanceof RedisAdapter) {
            $reflection = new \ReflectionClass($adapter);
            $redisProperty = $reflection->getProperty('redis');
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
     * Redis key prefix under which the Symfony adapter stores the actual cache DATA item.
     *
     * The data key is "<namespace>:<id>" (e.g. "792_:HOTKEY"), i.e. the tag-index namespace plus a
     * ':' separator — distinct from the tag/reverse index keys which concatenate the namespace with
     * no separator. Used by the existence guards that keep the index consistent with the data.
     *
     * @return string
     */
    private function dataKeyPrefix(): string
    {
        return $this->namespace . ':';
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
     * Size-adaptive intersection: uses SINTER when the smallest matching set is
     * small enough to be safe, falls back to SMEMBERS + PHP array_intersect when
     * any set is large. A bare SINTER on large sets blocks Valkey (single-threaded)
     * for the full O(N*M) duration, starving all other PHP workers.
     */
    public function getIdsMatchingTags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        if (count($tags) === 1) {
            return $this->toIdsArray($this->redis->sMembers($this->getTagKey($tags[0])));
        }

        $tagKeys = array_map([$this, 'getTagKey'], $tags);
        $sizes = $this->fetchSetSizes($tagKeys);

        // Any empty set means the intersection is empty
        if (in_array(0, $sizes, true)) {
            return [];
        }

        // Sort ascending so the smallest set is first — SINTER is most efficient that way
        array_multisort($sizes, SORT_ASC, $tagKeys);

        // When the smallest set is large, a single SINTER would block Valkey, so distribute
        // the work as multiple O(N) SMEMBERS calls intersected in PHP instead.
        if ($sizes[0] > self::SINTER_SAFE_SIZE) {
            return $this->intersectViaMembers($tagKeys);
        }

        return $this->toIdsArray($this->redis->sinter($tagKeys));
    }

    /**
     * Fetch the cardinality of every set in a single pipeline round-trip
     *
     * @param string[] $tagKeys
     * @return int[]
     */
    private function fetchSetSizes(array $tagKeys): array
    {
        $pipeline = $this->createPipeline();
        foreach ($tagKeys as $key) {
            $pipeline->scard($key);
        }

        return array_map('intval', (array)$this->executePipeline($pipeline));
    }

    /**
     * Intersect the given sets client-side using SMEMBERS + array_intersect
     *
     * @param string[] $tagKeys
     * @return string[]
     */
    private function intersectViaMembers(array $tagKeys): array
    {
        $pipeline = $this->createPipeline();
        foreach ($tagKeys as $key) {
            $pipeline->sMembers($key);
        }
        $sets = array_map([$this, 'toIdsArray'], (array)$this->executePipeline($pipeline));

        return array_values(array_intersect(...$sets));
    }

    /**
     * Normalize a Redis set result to a plain array of IDs
     *
     * @param mixed $ids
     * @return array
     */
    private function toIdsArray($ids): array
    {
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

                // Prune the index (tag sets + reverse index + all_ids) for this chunk so it does
                // not outlive its data. pruneTagIndex owns the all_ids removal — and, on the atomic
                // path, guards it on data-absence so a concurrent save is not clobbered (finding #4).
                $this->pruneTagIndex($chunk);

                // Commit each chunk separately (important for large operations)
                if (method_exists($this->cachePool, 'commit')) {
                    $this->cachePool->commit();
                }
            }

            return $success;
        }

        $success = $this->cachePool->deleteItems($ids);

        // Prune the index (tag sets + reverse index + all_ids) for the removed ids. all_ids removal
        // is currently handled inside pruneTagIndex (data-existence-guarded on the atomic path), so we
        // must NOT strip all_ids separately here — an unconditional srem would defeat the guard and
        // re-create the data-present / index-missing orphan (finding #4).
        $this->pruneTagIndex($ids);

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
            // Tagless entry: still register it in all_ids so getIdsNotMatchingTags() (and GC) can see
            // it. CLEANING_MODE_NOT_MATCHING_TAG must remove every entry that carries none of the
            // given tags, which includes untagged ones; without this they would be invisible to the
            // SDIFF and survive the clean. No forward tag SET or reverse index is written — there are
            // no tags to link.
            $this->registerId($id);
            return;
        }

        // Prefer the atomic, data-existence-guarded EVAL so a concurrent delete on the same id
        // cannot leave the index contradicting the data (finding #4). Falls back to the pipeline
        // when EVAL is unavailable (Predis/cluster) or fails.
        if ($this->supportsAtomicEval() && $this->onSaveAtomic($id, $tags)) {
            return;
        }

        $this->onSavePipelined($id, $tags);
    }

    /**
     * Register a (tagless) id in the all_ids set so NOT_MATCHING_TAG and GC can see it.
     *
     * Guarded on the data key existing so a failed or racing write cannot leave an all_ids entry
     * with no data behind it — the same invariant the tagged onSave path enforces. The EXISTS+SADD
     * pair is intentionally not atomic: a lost race only ever produces a transient orphan that
     * garbageCollect() prunes (it drops all_ids members whose data key is gone), so a heavier EVAL
     * is not warranted on this hot, per-save path.
     *
     * @param string $id
     * @return void
     */
    private function registerId(string $id): void
    {
        try {
            if ($this->redis->exists($this->dataKeyPrefix() . $id)) {
                $this->redis->sadd(self::ALL_IDS_SET, $id);
            }
        // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        } catch (\Throwable $e) {
            // Best-effort index maintenance; the next save or GC self-heals a missed registration.
        }
    }

    /**
     * Atomically (re)build the index for $id via LUA_ONSAVE, guarded on the data key existing.
     *
     * @param string $id
     * @param array $tags
     * @return bool True on success, false if EVAL failed (caller should fall back)
     */
    private function onSaveAtomic(string $id, array $tags): bool
    {
        try {
            $args = array_merge(
                [
                    $this->dataKeyPrefix() . $id,                       // ARGV[1] data key
                    $id,                                                // ARGV[2] id
                    self::ALL_IDS_SET,                                  // ARGV[3] all_ids
                    self::TAG_INDEX_PREFIX,                             // ARGV[4] tag prefix
                    $this->namespace,                                   // ARGV[5] namespace
                    self::REVERSE_INDEX_PREFIX . $this->namespace . $id // ARGV[6] reverse key
                ],
                array_values($tags)                                     // ARGV[7..] tag names
            );
            $this->evalNoKeys(self::LUA_ONSAVE, $args);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Non-atomic pipelined index build (fallback for Predis/cluster or when EVAL is disabled).
     *
     * @param string $id
     * @param array $tags
     * @return void
     */
    private function onSavePipelined(string $id, array $tags): void
    {
        $idTagsKey = self::REVERSE_INDEX_PREFIX . $this->namespace . $id;

        // Retag cleanup: read the id's previous tags and drop it from the forward SET of any tag it
        // no longer carries, so stale memberships cannot linger after a re-save with a different tag
        // set. A separate read is required because a pipeline cannot branch on a value; mirrors the
        // legacy Cm_Cache_Backend_Redis save() array_diff of old vs new tags.
        $oldTags = $this->toIdsArray($this->redis->sMembers($idTagsKey));
        $removedTags = array_diff($oldTags, $tags);

        $pipeline = $this->createPipeline();

        // Forward index: drop the id from tags it no longer has.
        foreach ($removedTags as $tag) {
            $pipeline->srem($this->getTagKey($tag), $id);
        }

        // Add ID to all_ids set
        $pipeline->sadd(self::ALL_IDS_SET, $id);

        // Forward index: Add ID to each tag's SET
        foreach ($tags as $tag) {
            $pipeline->sadd($this->getTagKey($tag), $id);
        }

        // Reverse index: replace the id's tag set with the new tags.
        $pipeline->del($idTagsKey);  // Clear old tags first
        foreach ($tags as $tag) {
            $pipeline->sadd($idTagsKey, $tag);
        }

        // Execute all operations in one go
        $this->executePipeline($pipeline);
    }

    /**
     * Bulk-prune the tag index for the given ids: remove each id from its tag SETs and delete its reverse-index key.
     *
     * Prefers an atomic EVAL and falls back to a pipelined path.
     *
     * @param array $ids
     * @return void
     */
    private function pruneTagIndex(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        // Prefer the atomic EVAL path: reading the reverse index and applying the removals in a
        // single script eliminates the read/write gap that let a concurrent onSave get clobbered.
        // Falls back to the pipelined path when EVAL is unavailable (Predis/cluster) or fails.
        if ($this->supportsAtomicEval() && $this->pruneTagIndexAtomic($ids)) {
            return;
        }

        $this->pruneTagIndexPipelined($ids);
    }

    /**
     * Atomically prune the tag index for the given ids via a single EVAL per chunk.
     *
     * @param array $ids
     * @return bool True on success, false if EVAL failed (caller should fall back)
     */
    private function pruneTagIndexAtomic(array $ids): bool
    {
        try {
            foreach (array_chunk(array_values($ids), self::LUA_MAX_CSTACK) as $chunk) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $args = array_merge(
                    [self::TAG_INDEX_PREFIX, $this->namespace, $this->dataKeyPrefix()],
                    $chunk
                );
                $this->evalNoKeys(self::LUA_PRUNE_INDEX, $args);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Non-atomic pipelined prune (fallback for Predis/cluster or when EVAL is disabled).
     *
     * Two pipelines: read the reverse index for every id, then apply removals. There is a
     * read/write gap here, so a concurrent save for the same id may still be clobbered; the
     * atomic path above is preferred whenever the client supports it.
     *
     * @param array $ids
     * @return void
     */
    private function pruneTagIndexPipelined(array $ids): void
    {
        // Read the reverse index (id -> tags) for every id in one pipeline
        $readPipe = $this->createPipeline();
        foreach ($ids as $id) {
            $readPipe->smembers(self::REVERSE_INDEX_PREFIX . $this->namespace . $id);
        }
        $tagsPerId = $this->executePipeline($readPipe);
        if (!is_array($tagsPerId)) {
            return;
        }

        // Drop each id from all_ids and its tag SETs, and delete its reverse index, in one
        // pipeline. all_ids removal is unconditional here (this fallback owns it now that
        // deleteByIds no longer removes it separately).
        $writePipe = $this->createPipeline();
        foreach (array_values($ids) as $i => $id) {
            $writePipe->srem(self::ALL_IDS_SET, $id);
            $tags = $tagsPerId[$i] ?? [];
            if (is_array($tags) && !empty($tags)) {
                foreach ($tags as $tag) {
                    $writePipe->srem($this->getTagKey($tag), $id);
                }
                $writePipe->del(self::REVERSE_INDEX_PREFIX . $this->namespace . $id);
            }
        }
        $this->executePipeline($writePipe);
    }

    /**
     * Whether the underlying client supports atomic single-node EVAL with computed keys.
     *
     * True for phpredis standalone (\Redis) and for Predis single-node/replication (EVAL runs on the
     * master). Excludes \RedisCluster: these scripts compute their keys from ARGV (numKeys=0), which
     * is unsafe across cluster slots, so a cluster still uses the pipelined fallback.
     *
     * @return bool
     */
    private function supportsAtomicEval(): bool
    {
        return $this->redis instanceof \Redis || $this->isPredisClient();
    }

    /**
     * Run a keyless Lua script (numKeys=0, every value passed as ARGV), normalizing the phpredis vs
     * Predis EVAL argument order and turning a Predis error reply (exceptions=false clients) into a
     * thrown exception so the callers' try/catch fallback behaves the same on both drivers.
     *
     * @param string $script
     * @param array $argv
     * @return mixed
     */
    private function evalNoKeys(string $script, array $argv)
    {
        return $this->evalScript($script, $argv, 0);
    }

    /**
     * Run a Lua script, normalizing the phpredis vs Predis EVAL argument order.
     *
     * Argument order: phpredis uses eval($script, $keysAndArgs, $numKeys); Predis uses
     * eval($script, $numKeys, ...$keysAndArgs).
     *
     * @param string $script
     * @param array $keysAndArgs Flat list: the $numKeys KEYS first, then the ARGV values
     * @param int $numKeys
     * @return mixed
     */
    private function evalScript(string $script, array $keysAndArgs, int $numKeys)
    {
        if ($this->isPredisClient()) {
            return $this->unwrapPredisReply($this->redis->eval($script, $numKeys, ...$keysAndArgs));
        }
        return $this->redis->eval($script, $keysAndArgs, $numKeys);
    }

    /**
     * Run a cached Lua script by SHA, normalizing the phpredis vs Predis EVALSHA argument order.
     *
     * @param string $sha
     * @param array $keysAndArgs Flat list: the $numKeys KEYS first, then the ARGV values
     * @param int $numKeys
     * @return mixed
     */
    private function evalShaScript(string $sha, array $keysAndArgs, int $numKeys)
    {
        if ($this->isPredisClient()) {
            return $this->unwrapPredisReply($this->redis->evalsha($sha, $numKeys, ...$keysAndArgs));
        }
        return $this->redis->evalSha($sha, $keysAndArgs, $numKeys);
    }

    /**
     * SCRIPT LOAD a Lua script and return its SHA, normalizing the Predis error reply.
     *
     * @param string $script
     * @return mixed SHA string
     */
    private function scriptLoad(string $script)
    {
        if ($this->isPredisClient()) {
            return $this->unwrapPredisReply($this->redis->script('load', $script));
        }
        return $this->redis->script('load', $script);
    }

    /**
     * Turn a Predis error reply (exceptions=false clients) into a thrown exception.
     *
     * This way callers' try/catch fallbacks fire identically on phpredis and Predis.
     *
     * @param mixed $result
     * @return mixed
     */
    private function unwrapPredisReply($result)
    {
        if ($result instanceof \Predis\Response\ErrorInterface) {
            throw new \RuntimeException((string)$result->getMessage());
        }
        return $result;
    }

    /**
     * Iterate keys matching $pattern using SCAN, yielding them in batches.
     *
     * Cursor-based and non-blocking, normalizing phpredis vs Predis. Deliberately avoids the KEYS
     * command: KEYS is O(N) over the WHOLE keyspace and blocks
     * single-threaded Redis/Valkey for the entire scan, which would starve PHP workers when it runs
     * from the backend_clean_cache cron / cache:flush. Legacy Cm_Cache_Backend_Redis GCs the same way
     * (SSCAN over maintained sets), never KEYS. Errors are allowed to propagate so a failed sweep is
     * surfaced, not masked.
     *
     * @param string $pattern
     * @param int $count SCAN COUNT hint (batch size)
     * @return \Generator<int, array>
     */
    private function scanKeys(string $pattern, int $count): \Generator
    {
        if ($this->isPredisClient()) {
            $cursor = '0';
            do {
                [$cursor, $keys] = $this->unwrapPredisReply(
                    $this->redis->scan($cursor, ['MATCH' => $pattern, 'COUNT' => $count])
                );
                if (!empty($keys)) {
                    yield $keys;
                }
            } while ((string)$cursor !== '0');
            return;
        }

        // phpredis: scan() returns a batch (possibly empty) and advances $iterator by reference,
        // returning false once the full iteration is complete.
        $iterator = null;
        while (($keys = $this->redis->scan($iterator, $pattern, $count)) !== false) {
            if (!empty($keys)) {
                yield $keys;
            }
        }
    }

    /**
     * Iterate the members of a Redis SET with SSCAN (cursor-based, non-blocking), normalizing phpredis
     * vs Predis, and yield them in batches. This is how legacy Cm_Cache_Backend_Redis GC-scans its
     * id/tag sets — it visits only the set's members, never the whole keyspace.
     *
     * @param string $key SET key
     * @param int $count SSCAN COUNT hint (batch size)
     * @return \Generator<int, array>
     */
    private function sscan(string $key, int $count): \Generator
    {
        if ($this->isPredisClient()) {
            $cursor = '0';
            do {
                [$cursor, $members] = $this->unwrapPredisReply(
                    $this->redis->sscan($key, $cursor, ['COUNT' => $count])
                );
                if (!empty($members)) {
                    yield $members;
                }
            } while ((string)$cursor !== '0');
            return;
        }

        // phpredis: sScan() returns a batch of members (possibly empty) and advances $iterator by
        // reference, returning false once the full iteration is complete.
        $iterator = null;
        while (($members = $this->redis->sScan($key, $iterator, null, $count)) !== false) {
            if (!empty($members)) {
                yield $members;
            }
        }
    }

    /**
     * Acquire the stale-cache regeneration lock atomically (SET key token NX EX ttl).
     *
     * Returns true for exactly one caller cluster-wide; the token identifies the owner so the
     * lock can be released safely later. Any client error is treated as "not acquired".
     *
     * @param string $id Cache id being regenerated
     * @param string $token Per-process ownership token
     * @param int $ttl Lock lifetime in seconds (auto-expiry if the owner dies)
     * @return bool
     */
    public function acquireLock(string $id, string $token, int $ttl): bool
    {
        $key = self::REGEN_LOCK_PREFIX . $this->namespace . $id;

        try {
            $result = $this->isPredisClient()
                ? $this->redis->set($key, $token, 'EX', $ttl, 'NX')
                : $this->redis->set($key, $token, ['NX', 'EX' => $ttl]);

            // phpredis returns true/false; Predis returns a Status('OK') on set, null when NX fails
            return $result !== null && $result !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Release the regeneration lock only if this process still owns it (ownership-safe delete).
     *
     * Uses a GET+DEL compare-and-delete in a single EVAL so a lock re-acquired by another owner
     * (e.g. after TTL expiry) is never deleted out from under it.
     *
     * @param string $id Cache id
     * @param string $token The token used when acquiring
     * @return bool True if this process owned the lock and it was released
     */
    public function releaseLock(string $id, string $token): bool
    {
        $key = self::REGEN_LOCK_PREFIX . $this->namespace . $id;
        $lua = "if redis.call('GET', KEYS[1]) == ARGV[1] then return redis.call('DEL', KEYS[1]) else return 0 end";

        try {
            $result = $this->isPredisClient()
                ? $this->redis->eval($lua, 1, $key, $token)
                : $this->redis->eval($lua, [$key, $token], 1);

            return (int)$result === 1;
        } catch (\Throwable $e) {
            return false;
        }
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

        // Fallback: PHP-based clearing. Enumerate with SCAN (cursor-based, non-blocking) rather than
        // KEYS — this runs on every cache:flush / clean(ALL), and KEYS would block Redis/Valkey across
        // the whole keyspace. Delete each batch as it is scanned.
        $tagPattern = self::TAG_INDEX_PREFIX . $this->namespace . '*';
        foreach ($this->scanKeys($tagPattern, 1000) as $chunk) {
            // PHP 8+ compatibility: use call_user_func_array to avoid spread operator issues
            call_user_func_array([$this->redis, 'del'], $chunk);
        }

        // Clear all_ids set
        $this->redis->del(self::ALL_IDS_SET);

        // Clear reverse index keys
        $reversePattern = self::REVERSE_INDEX_PREFIX . $this->namespace . '*';
        foreach ($this->scanKeys($reversePattern, 1000) as $chunk) {
            call_user_func_array([$this->redis, 'del'], $chunk);
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
        // Always sweep via the reverse index. We deliberately do NOT use the Lua keyspace-scan GC
        // (use_lua_on_gc): that script does SCAN MATCH "<namespace>*" and prunes keys whose TTL == -2,
        // but a TTL-expired data key has already been removed from the keyspace by Redis, so SCAN
        // never returns it — it can never find these orphans (it prunes 0). The tag SETs and reverse
        // index persist after the data expires, so we walk the reverse index and prune the ids whose
        // data key is gone. Mirrors legacy Cm Redis _collectGarbage() (which likewise reasons from the
        // tag/id sets, not a keyspace scan of already-deleted keys).
        return $this->garbageCollectClientSide($batchSize);
    }

    /**
     * @inheritDoc
     *
     * Matches legacy Cm_Cache_Backend_Redis::getFillingPercentage() exactly: reads the configured
     * ceiling via CONFIG GET (not INFO's maxmemory field, which is not always populated), and returns 1
     * — not 0 — when no ceiling is configured, since "unlimited" has no meaningful fullness fraction and
     * legacy treated that as effectively empty rather than ambiguous.
     */
    public function getFillingPercentage(): int
    {
        try {
            $configReply = $this->unwrapPredisReply($this->redis->config('GET', 'maxmemory'));
            $maxMemory = (int)($configReply['maxmemory'] ?? 0);
            if ($maxMemory <= 0) {
                return 1;
            }

            $info = $this->unwrapPredisReply($this->redis->info());
            $usedMemory = (int)($this->isPredisClient()
                ? ($info['Memory']['used_memory'] ?? 0)
                : ($info['used_memory'] ?? 0));

            return (int)round($usedMemory / $maxMemory * 100);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Non-Lua garbage collection: prune tag-index members whose data key no longer exists.
     *
     * Enumerates the maintained id set (cache:all_ids) with SSCAN — the direct analogue of legacy Cm
     * Redis GC-scanning its zc:ids set, and the same set getIdsNotMatchingTags() already treats as this
     * frontend's ids. SSCAN visits only the set's members (not the whole keyspace) and never issues the
     * blocking KEYS. For each id whose data key has TTL-expired it hands the id to pruneTagIndex(),
     * which removes it from its tag SETs, reverse index and all_ids. Only ids confirmed missing are
     * pruned, so live entries are never touched; the data-key derivation (dataKeyPrefix() . id) is the
     * same one the atomic prune's EXISTS guard relies on.
     *
     * A scan/prune error is intentionally NOT swallowed: it propagates so garbageCollect() ->
     * clean(OLD) reports a FAILURE (the cron logs it) rather than a successful no-op while the tag
     * index silently grows. Mirrors legacy, which throws on a GC error (Zend_Cache::throwException).
     *
     * @param int $batchSize
     * @return int Number of orphaned index entries removed
     */
    private function garbageCollectClientSide(int $batchSize): int
    {
        $dataPrefix = $this->dataKeyPrefix();
        $removed = 0;

        foreach ($this->sscan(self::ALL_IDS_SET, max(1, $batchSize)) as $members) {
            // EXISTS-check every id's data key in one pipeline.
            $ids = array_values($members);
            $pipe = $this->createPipeline();
            foreach ($ids as $id) {
                $pipe->exists($dataPrefix . $id);
            }
            $exists = $this->executePipeline($pipe);
            if (!is_array($exists)) {
                continue;
            }

            $expired = [];
            foreach ($ids as $i => $id) {
                // phpredis EXISTS returns 0/1 (int); a falsy value means the data key is gone.
                if (empty($exists[$i])) {
                    $expired[] = $id;
                }
            }

            if (!empty($expired)) {
                $this->pruneTagIndex($expired);
                $removed += count($expired);
            }
        }

        return $removed;
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

        // phpredis expects a SINGLE [KEYS..., ARGV...] array plus the KEY count; passing ARGV as
        // extra positional params (the previous code) throws ArgumentCountError and never reaches
        // the script. ARGV order: [tag_prefix, namespace, chunk_size].
        $args = array_merge($tags, [self::TAG_INDEX_PREFIX, $this->namespace, 100]);

        try {
            $sha = $this->loadLuaScript(self::LUA_CLEAN_MATCHING_ANY_TAGS);
            return (int)$this->evalShaScript($sha, $args, count($tags));
        } catch (\Throwable $e) {
            // Fallback: try executing the script directly
            try {
                return (int)$this->evalScript(self::LUA_CLEAN_MATCHING_ANY_TAGS, $args, count($tags));
            } catch (\Throwable $e) {
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

        // Single [KEYS..., ARGV...] array + KEY count (see cleanMatchingAnyTagsLua).
        // ARGV order: [tag_prefix, namespace, scope_tag].
        $args = array_merge($tags, [self::TAG_INDEX_PREFIX, $this->namespace, $scopeTag]);

        try {
            $sha = $this->loadLuaScript(self::LUA_CLEAN_MATCHING_ANY_TAGS_WITH_SCOPE);
            return (int)$this->evalShaScript($sha, $args, count($tags));
        } catch (\Throwable $e) {
            // Fallback: try executing script directly
            try {
                return (int)$this->evalScript(self::LUA_CLEAN_MATCHING_ANY_TAGS_WITH_SCOPE, $args, count($tags));
            } catch (\Throwable $e) {
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
     * @throws \RuntimeException
     */
    private function loadLuaScript(string $script): string
    {
        try {
            return (string)$this->scriptLoad($script);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to load Lua script: ' . $e->getMessage(), 0, $e);
        }
    }
}
