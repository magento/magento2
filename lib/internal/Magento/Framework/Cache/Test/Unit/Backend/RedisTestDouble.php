<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Backend;

/**
 * Minimal in-memory Credis_Client double used to exercise the tag-index cleanup logic without a
 * real Redis connection. Only implements the small subset of commands the SUT relies on.
 */
class RedisTestDouble
{
    private const PREFIX_KEY = 'zc:k:';
    private const PREFIX_TAG_IDS = 'zc:ti:';

    /** @var array<int, array> Recorded (command, ...args) tuples, in call order. */
    public array $calls = [];

    /** @var array|false|null When set (non-null), overrides the next exec() return value once. */
    public array|false|null $execReturnOverride = null;

    /** @var bool|array */
    public bool|array $evalShaResult = true;

    /** @var array<string, string> id => raw (uncompressed) encoded tags */
    private array $hashes = [];

    /** @var array<string, string[]> tag => member ids */
    private array $sets = [];

    /** @var bool */
    private bool $inPipeline = false;

    /** @var array<int, mixed> */
    private array $pending = [];

    /**
     * Seed a cache entry's stored tag list.
     *
     * @param string $id
     * @param string $tags
     * @return void
     */
    public function seedHash(string $id, string $tags): void
    {
        $this->hashes[$id] = $tags;
    }

    /**
     * Seed a tag's id index.
     *
     * @param string $tag
     * @param array $ids
     * @return void
     */
    public function seedSet(string $tag, array $ids): void
    {
        $this->sets[$tag] = $ids;
    }

    /**
     * Check whether the cache entry still exists.
     *
     * @param string $id
     * @return bool
     */
    public function hashExists(string $id): bool
    {
        return isset($this->hashes[$id]);
    }

    /**
     * Read the members of a tag's id index.
     *
     * @param string $tag
     * @return array
     */
    public function setMembers(string $tag): array
    {
        return array_values($this->sets[$tag] ?? []);
    }

    /**
     * Start collecting pipeline responses.
     *
     * @return self
     */
    public function pipeline(): self
    {
        $this->inPipeline = true;
        $this->pending = [];

        return $this;
    }

    /**
     * Simulate the transaction builder used by the backend.
     *
     * @return self
     */
    public function multi(): self
    {
        return $this;
    }

    /**
     * Read a cache entry's tag field, or queue its response in a pipeline.
     *
     * @param string $key
     * @param string $field
     * @return string|false|self
     */
    public function hGet(string $key, string $field): string|false|self
    {
        $this->calls[] = ['hGet', $key, $field];
        $id = $this->stripPrefix($key, self::PREFIX_KEY);
        $value = $this->hashes[$id] ?? false;

        if ($this->inPipeline) {
            $this->pending[] = $value;

            return $this;
        }

        return $value;
    }

    /**
     * Remove cache data or tag-index keys.
     *
     * @param string|array $keys
     * @return int|self
     */
    public function unlink(string|array $keys): int|self
    {
        $this->calls[] = ['unlink', $keys];
        foreach ((array) $keys as $key) {
            unset($this->hashes[$this->stripPrefix($key, self::PREFIX_KEY)]);
            unset($this->sets[$this->stripPrefix($key, self::PREFIX_TAG_IDS)]);
        }

        if ($this->inPipeline) {
            $this->pending[] = 1;

            return $this;
        }

        return 1;
    }

    /**
     * Remove ids from a tag index.
     *
     * @param string $key
     * @param string|array $members
     * @return int|self
     */
    public function sRem(string $key, string|array $members): int|self
    {
        $this->calls[] = ['sRem', $key, $members];
        $tag = $this->stripPrefix($key, self::PREFIX_TAG_IDS);
        $this->sets[$tag] = array_values(array_diff($this->sets[$tag] ?? [], (array) $members));

        if ($this->inPipeline) {
            $this->pending[] = 1;

            return $this;
        }

        return 1;
    }

    /**
     * Find ids matching any of the given tag-index keys.
     *
     * @param string|array $keys
     * @return array
     */
    public function sUnion(string|array $keys): array
    {
        $this->calls[] = ['sUnion', $keys];
        $union = [];
        foreach ((array) $keys as $key) {
            foreach ($this->sets[$this->stripPrefix($key, self::PREFIX_TAG_IDS)] ?? [] as $member) {
                $union[] = $member;
            }
        }

        return array_values(array_unique($union));
    }

    /**
     * Record an upstream Lua script invocation.
     *
     * @param mixed ...$args
     * @return bool|array
     */
    public function evalSha(...$args): bool|array
    {
        $this->calls[] = ['evalSha', $args];

        return $this->evalShaResult;
    }

    /**
     * Record a fallback Lua script invocation.
     *
     * @param mixed ...$args
     * @return bool
     */
    public function eval(...$args): bool
    {
        $this->calls[] = ['eval', $args];

        return true;
    }

    /**
     * Return queued pipeline responses or the configured failure response.
     *
     * @return array|false
     */
    public function exec(): array|false
    {
        $this->calls[] = ['exec'];
        $this->inPipeline = false;

        if ($this->execReturnOverride !== null) {
            $result = $this->execReturnOverride;
            $this->execReturnOverride = null;

            return $result;
        }

        $result = $this->pending;
        $this->pending = [];

        return $result;
    }

    /**
     * Convert a Redis key back to a seeded id or tag.
     *
     * @param string $key
     * @param string $prefix
     * @return string
     */
    private function stripPrefix(string $key, string $prefix): string
    {
        return str_starts_with($key, $prefix) ? substr($key, strlen($prefix)) : $key;
    }
}
