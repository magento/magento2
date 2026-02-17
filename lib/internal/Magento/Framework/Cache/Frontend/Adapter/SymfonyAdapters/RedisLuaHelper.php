<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters;

use InvalidArgumentException;

/**
 * Redis Lua script helper for advanced atomic operations
 *
 * Provides Lua script functionality for operations that benefit from
 * server-side execution and true atomicity beyond what pipelines offer.
 */
class RedisLuaHelper
{
    /**
     * Redis connection
     *
     * @var mixed Redis connection object
     */
    private $redis;

    /**
     * @var array
     */
    private array $scriptShas = [];

    /**
     * @var bool Whether Lua scripts are enabled
     */
    private bool $enabled;

    /**
     * Lua script for atomic tag-based deletion with conditions
     *
     * Deletes cache items by tag only if they match additional conditions
     * (e.g., expired, or have specific metadata)
     */
    private const SCRIPT_CLEAN_BY_TAG_CONDITIONAL = <<<'LUA'
-- KEYS[1]: tag set key (e.g., "cache:tags:69d_config")
-- KEYS[2]: namespace prefix (e.g., "69d_")
-- ARGV[1]: current timestamp (for TTL checks)
-- ARGV[2]: condition type ("expired"|"all")

local tag_key = KEYS[1]
local prefix = KEYS[2]
local now = tonumber(ARGV[1])
local condition = ARGV[2]

-- Get all IDs for this tag
local ids = redis.call('SMEMBERS', tag_key)
local deleted = 0

for _, id in ipairs(ids) do
    local cache_key = prefix .. id
    local should_delete = false

    if condition == "all" then
        should_delete = true
    elseif condition == "expired" then
        -- Check if key expired (has TTL <= 0)
        local ttl = redis.call('TTL', cache_key)
        if ttl == -2 or ttl == -1 then
            should_delete = true
        end
    end

    if should_delete then
        redis.call('DEL', cache_key)
        redis.call('SREM', tag_key, id)
        deleted = deleted + 1
    end
end

return deleted
LUA;

    /**
     * Lua script for atomic save with tag cleanup
     *
     * Saves item, removes it from old tags, adds to new tags, all atomically
     */
    private const SCRIPT_ATOMIC_SAVE_WITH_TAGS = <<<'LUA'
-- KEYS[1]: cache key
-- KEYS[2]: old tags set key (reverse index)
-- ARGV[1]: cache value
-- ARGV[2]: TTL in seconds
-- ARGV[3+]: new tag keys

local cache_key = KEYS[1]
local old_tags_key = KEYS[2]
local value = ARGV[1]
local ttl = tonumber(ARGV[2])

-- Get old tags
local old_tags = redis.call('SMEMBERS', old_tags_key)

-- Remove item from old tag sets
for _, tag_key in ipairs(old_tags) do
    redis.call('SREM', tag_key, cache_key)
end

-- Save cache item
redis.call('SET', cache_key, value)
if ttl > 0 then
    redis.call('EXPIRE', cache_key, ttl)
end

-- Add to new tag sets
for i = 3, #ARGV do
    local new_tag_key = ARGV[i]
    redis.call('SADD', new_tag_key, cache_key)
end

-- Update reverse index with new tags
redis.call('DEL', old_tags_key)
for i = 3, #ARGV do
    redis.call('SADD', old_tags_key, ARGV[i])
end

return 1
LUA;

    /**
     * Lua script for efficient garbage collection
     *
     * Finds and deletes expired cache items and cleans up tag indices
     */
    private const SCRIPT_GARBAGE_COLLECT = <<<'LUA'
-- KEYS[1]: pattern to scan (e.g., "69d_*")
-- KEYS[2]: tag prefix (e.g., "cache:tags:69d_")
-- ARGV[1]: batch size
-- ARGV[2]: cursor (for pagination)

local pattern = KEYS[1]
local tag_prefix = KEYS[2]
local batch_size = tonumber(ARGV[1])
local cursor = ARGV[2]

-- Scan for keys matching pattern
local result = redis.call('SCAN', cursor, 'MATCH', pattern, 'COUNT', batch_size)
local next_cursor = result[1]
local keys = result[2]

local deleted = 0

for _, key in ipairs(keys) do
    -- Check if key is expired or doesn't exist
    local ttl = redis.call('TTL', key)
    if ttl == -2 then
        -- Key expired or doesn't exist
        -- Extract ID from key (remove prefix)
        local id = string.gsub(key, "^" .. string.match(KEYS[1], "^(.-)%*"), "")

        -- Find and remove from tag indices
        local tag_pattern = tag_prefix .. "*"
        local tag_scan = redis.call('SCAN', 0, 'MATCH', tag_pattern, 'COUNT', 100)
        local tag_keys = tag_scan[2]

        for _, tag_key in ipairs(tag_keys) do
            redis.call('SREM', tag_key, id)
        end

        deleted = deleted + 1
    end
end

return {next_cursor, deleted}
LUA;

    /**
     * Lua script for clearing all cache indices atomically
     *
     * Efficiently removes all tag indices, reverse indices, and all_ids set
     * Uses SCAN to handle large datasets without blocking Redis
     */
    private const SCRIPT_CLEAR_ALL_INDICES = <<<'LUA'
-- KEYS[1]: tag pattern (e.g., "cache:tags:69d_*")
-- KEYS[2]: reverse index pattern (e.g., "cache:id_tags:69d_*")
-- KEYS[3]: all_ids set key (e.g., "cache:all_ids")
-- ARGV[1]: batch size for SCAN operations

local tag_pattern = KEYS[1]
local reverse_pattern = KEYS[2]
local all_ids_key = KEYS[3]
local batch_size = tonumber(ARGV[1]) or 1000

local total_deleted = 0

-- Helper function to delete keys matching pattern using SCAN
local function delete_by_pattern(pattern)
    local cursor = "0"
    local deleted = 0
    local iterations = 0
    local max_iterations = 100  -- Safety limit

    repeat
        local result = redis.call('SCAN', cursor, 'MATCH', pattern, 'COUNT', batch_size)
        cursor = result[1]
        local keys = result[2]

        if #keys > 0 then
            redis.call('DEL', unpack(keys))
            deleted = deleted + #keys
        end

        iterations = iterations + 1
    until cursor == "0" or iterations >= max_iterations

    return deleted
end

-- Delete all tag indices (cache:tags:*)
total_deleted = total_deleted + delete_by_pattern(tag_pattern)

-- Delete all reverse indices (cache:id_tags:*)
total_deleted = total_deleted + delete_by_pattern(reverse_pattern)

-- Delete all_ids set
if redis.call('EXISTS', all_ids_key) == 1 then
    redis.call('DEL', all_ids_key)
    total_deleted = total_deleted + 1
end

return total_deleted
LUA;

    /**
     * Constructor
     *
     * Note: Uses untyped parameter to avoid DI compilation issues with PHP extension classes
     *
     * @param mixed $redis Redis connection from Symfony RedisAdapter
     * @param bool $enabled Whether Lua scripts are enabled
     */
    public function __construct($redis, bool $enabled = true)
    {
        // Runtime type check using get_class to avoid referencing Redis class directly
        // This prevents DI compilation errors with PHP extension classes
        if (!is_object($redis) || get_class($redis) !== 'Redis') {
            throw new InvalidArgumentException('Redis connection must be an instance of Redis');
        }

        $this->redis = $redis;
        $this->enabled = $enabled;
    }

    /**
     * Check if Lua scripts are enabled and supported
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            // Test if Lua is supported
            $this->redis->eval('return 1', [], 0);
            return true;
        } catch (\RedisException $e) {
            return false;
        }
    }

    /**
     * Clean cache items by tag with conditions
     *
     * @param string $tagKey Tag set key
     * @param string $prefix Cache key prefix
     * @param string $condition "expired"|"all"
     * @return int Number of items deleted
     */
    public function cleanByTagConditional(
        string $tagKey,
        string $prefix,
        string $condition = 'all'
    ): int {
        if (!$this->isEnabled()) {
            return 0;
        }

        $sha = $this->loadScript(self::SCRIPT_CLEAN_BY_TAG_CONDITIONAL);

        try {
            $result = $this->redis->evalSha(
                $sha,
                [$tagKey, $prefix, time(), $condition],
                2  // Number of KEYS
            );

            return (int)$result;
        } catch (\RedisException $e) {
            // Fallback: script not loaded, try eval
            return (int)$this->redis->eval(
                self::SCRIPT_CLEAN_BY_TAG_CONDITIONAL,
                [$tagKey, $prefix, time(), $condition],
                2
            );
        }
    }

    /**
     * Atomic save with tag management
     *
     * Saves item and updates tag indices atomically
     *
     * @param string $cacheKey
     * @param string $value
     * @param int $ttl
     * @param array $newTagKeys
     * @param string $reverseIndexKey
     * @return bool
     */
    public function atomicSaveWithTags(
        string $cacheKey,
        string $value,
        int $ttl,
        array $newTagKeys,
        string $reverseIndexKey
    ): bool {
        if (!$this->isEnabled()) {
            return false;
        }

        $sha = $this->loadScript(self::SCRIPT_ATOMIC_SAVE_WITH_TAGS);

        $argv = array_merge([$value, $ttl], $newTagKeys);

        try {
            $result = $this->redis->evalSha(
                $sha,
                array_merge([$cacheKey, $reverseIndexKey], $argv),
                2  // Number of KEYS
            );

            return (bool)$result;
        } catch (\RedisException $e) {
            // Fallback: script not loaded
            return (bool)$this->redis->eval(
                self::SCRIPT_ATOMIC_SAVE_WITH_TAGS,
                array_merge([$cacheKey, $reverseIndexKey], $argv),
                2
            );
        }
    }

    /**
     * Efficient garbage collection using Lua
     *
     * @param string $pattern Key pattern (e.g., "69d_*")
     * @param string $tagPrefix Tag prefix (e.g., "cache:tags:69d_")
     * @param int $batchSize
     * @return array [total_deleted, iterations]
     */
    public function garbageCollect(
        string $pattern,
        string $tagPrefix,
        int $batchSize = 1000
    ): array {
        if (!$this->isEnabled()) {
            return [0, 0];
        }

        $sha = $this->loadScript(self::SCRIPT_GARBAGE_COLLECT);

        $totalDeleted = 0;
        $iterations = 0;
        $cursor = '0';

        do {
            try {
                $result = $this->redis->evalSha(
                    $sha,
                    [$pattern, $tagPrefix, $batchSize, $cursor],
                    2  // Number of KEYS
                );

                $cursor = $result[0];
                $deleted = $result[1];
                $totalDeleted += $deleted;
                $iterations++;

                // Safety: max 100 iterations
                if ($iterations >= 100) {
                    break;
                }
            } catch (\RedisException $e) {
                // Fallback or break on error
                break;
            }
        } while ($cursor !== '0');

        return [$totalDeleted, $iterations];
    }

    /**
     * Clear all cache indices using Lua script
     *
     * Atomically removes all tag indices, reverse indices, and all_ids set
     * More efficient than PHP-based iteration for large datasets
     *
     * @param string $namespace Cache namespace/prefix (e.g., "69d_")
     * @param int $batchSize Batch size for SCAN operations
     * @return int Total number of keys deleted
     */
    public function clearAllIndices(string $namespace, int $batchSize = 1000): int
    {
        if (!$this->isEnabled()) {
            return 0;
        }

        $sha = $this->loadScript(self::SCRIPT_CLEAR_ALL_INDICES);

        // Build patterns
        $tagPattern = 'cache:tags:' . $namespace . '*';
        $reversePattern = 'cache:id_tags:' . $namespace . '*';
        $allIdsKey = 'cache:all_ids';

        try {
            $result = $this->redis->evalSha(
                $sha,
                [$tagPattern, $reversePattern, $allIdsKey, $batchSize],
                3  // Number of KEYS
            );

            return (int)$result;
        } catch (\RedisException $e) {
            // Fallback: run script directly
            try {
                $result = $this->redis->eval(
                    self::SCRIPT_CLEAR_ALL_INDICES,
                    [$tagPattern, $reversePattern, $allIdsKey, $batchSize],
                    3
                );
                return (int)$result;
            } catch (\RedisException $e) {
                // Script execution failed
                return 0;
            }
        }
    }

    /**
     * Load script and return SHA1
     *
     * @param string $script
     * @return string SHA1 of the script
     */
    private function loadScript(string $script): string
    {
        $hash = hash('sha256', $script);

        if (isset($this->scriptShas[$hash])) {
            return $this->scriptShas[$hash];
        }

        try {
            $sha = $this->redis->script('load', $script);
            $this->scriptShas[$hash] = $sha;
            return $sha;
        } catch (\RedisException $e) {
            throw new \RuntimeException('Failed to load Lua script: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Clear all cached script SHAs
     *
     * Call this if Redis SCRIPT FLUSH is executed
     *
     * @return void
     */
    public function clearScriptCache(): void
    {
        $this->scriptShas = [];
    }
}
