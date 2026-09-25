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

    /** @var mixed When set (non-null), overrides the next exec() return value once. */
    public $execReturnOverride;

    /** @var bool|array */
    public $evalShaResult = true;

    /** @var array<string, string> id => raw (uncompressed) encoded tags */
    private array $hashes = [];

    /** @var array<string, string[]> tag => member ids */
    private array $sets = [];

    /** @var bool */
    private bool $inPipeline = false;

    /** @var array<int, mixed> */
    private array $pending = [];

    public function seedHash(string $id, string $tags): void
    {
        $this->hashes[$id] = $tags;
    }

    public function seedSet(string $tag, array $ids): void
    {
        $this->sets[$tag] = $ids;
    }

    public function hashExists(string $id): bool
    {
        return isset($this->hashes[$id]);
    }

    public function setMembers(string $tag): array
    {
        return array_values($this->sets[$tag] ?? []);
    }

    public function pipeline(): self
    {
        $this->inPipeline = true;
        $this->pending = [];

        return $this;
    }

    public function multi(): self
    {
        return $this;
    }

    public function hGet(string $key, string $field)
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

    public function unlink($keys)
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

    public function sRem(string $key, $members)
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

    public function sUnion($keys)
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

    public function evalSha(...$args)
    {
        $this->calls[] = ['evalSha', $args];

        return $this->evalShaResult;
    }

    public function eval(...$args)
    {
        $this->calls[] = ['eval', $args];

        return true;
    }

    public function exec()
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

    private function stripPrefix(string $key, string $prefix): string
    {
        return str_starts_with($key, $prefix) ? substr($key, strlen($prefix)) : $key;
    }
}
