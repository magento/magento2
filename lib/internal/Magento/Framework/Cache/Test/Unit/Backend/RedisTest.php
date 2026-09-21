<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Backend;

use Magento\Framework\Cache\Backend\Redis;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Unit tests for AC-18197: stale tag-index cleanup in the legacy Redis cache backend.
 *
 * Covers _removeByIds(), _fetchTagsPerId(), _groupIdsByTag() and _removeByMatchingAnyTags().
 */
class RedisTest extends TestCase
{
    /**
     * _removeByIds() must delete the data keys AND remove the ids from every tag's id-index,
     * not just the tags supplied by the caller, while leaving unrelated members untouched.
     */
    public function testRemoveByIdsCleansDataAndFullTagIndex(): void
    {
        $redis = new RedisTestDouble();
        $redis->seedHash('id1', 'tagA,tagB');
        $redis->seedHash('id2', 'tagB');
        $redis->seedSet('tagA', ['id1', 'id_untouched']);
        $redis->seedSet('tagB', ['id1', 'id2', 'id_untouched']);

        $backend = $this->createBackend($redis);

        $this->invokeMethod($backend, '_removeByIds', [['id1', 'id2']]);

        $this->assertFalse($redis->hashExists('id1'));
        $this->assertFalse($redis->hashExists('id2'));
        $this->assertSame(['id_untouched'], $redis->setMembers('tagA'));
        $this->assertSame(['id_untouched'], $redis->setMembers('tagB'));
    }

    /**
     * An empty id list must be a no-op (no Redis round trips at all).
     */
    public function testRemoveByIdsWithEmptyArrayIsNoop(): void
    {
        $redis = new RedisTestDouble();
        $backend = $this->createBackend($redis);

        $this->invokeMethod($backend, '_removeByIds', [[]]);

        $this->assertSame([], $redis->calls);
    }

    /**
     * _fetchTagsPerId() must return the raw hGet results in the same order as the input ids.
     */
    public function testFetchTagsPerIdReturnsResultsInOrder(): void
    {
        $redis = new RedisTestDouble();
        $redis->seedHash('id1', 'tagA');
        $redis->seedHash('id2', 'tagB,tagC');

        $backend = $this->createBackend($redis);

        $result = $this->invokeMethod($backend, '_fetchTagsPerId', [['id1', 'id2']]);

        $this->assertSame(['tagA', 'tagB,tagC'], $result);
    }

    /**
     * A failed/partial pipeline exec() must degrade gracefully to an empty result (skipping tag
     * cleanup for that chunk) instead of casting a falsy value straight into a bogus array, and
     * must surface the failure via a warning rather than failing silently.
     */
    public function testFetchTagsPerIdSurfacesPipelineFailure(): void
    {
        $redis = new RedisTestDouble();
        $redis->seedHash('id1', 'tagA');
        $redis->execReturnOverride = false;

        $backend = $this->createBackend($redis);

        $warnings = [];
        set_error_handler(function (int $errno, string $errstr) use (&$warnings): bool {
            $warnings[] = $errstr;
            return true;
        }, E_USER_WARNING);

        try {
            $result = $this->invokeMethod($backend, '_fetchTagsPerId', [['id1']]);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $result);
        $this->assertNotEmpty($warnings, 'A pipeline failure must be surfaced via a warning');
    }

    /**
     * _groupIdsByTag() must invert the id => tags mapping into tag => ids, ignoring empty tags.
     */
    public function testGroupIdsByTagInvertsMapping(): void
    {
        $redis = new RedisTestDouble();
        $backend = $this->createBackend($redis);

        $result = $this->invokeMethod(
            $backend,
            '_groupIdsByTag',
            [['id1', 'id2', 'id3'], ['tagA,tagB', 'tagB', false]]
        );

        $this->assertSame(
            ['tagA' => ['id1'], 'tagB' => ['id1', 'id2']],
            $result
        );
    }

    /**
     * The non-Lua fallback of _removeByMatchingAnyTags() must clean up the id-index of every tag
     * a matched id belongs to, including tags outside the ones passed to the clean() call
     * (AC-18197 regression: upstream only cleared the passed-in tags' sets).
     */
    public function testRemoveByMatchingAnyTagsNonLuaCleansTagsOutsideThePassedSet(): void
    {
        $redis = new RedisTestDouble();
        // id1 belongs to tag1 (passed) AND tag3 (not passed).
        $redis->seedHash('id1', 'tag1,tag3');
        $redis->seedSet('tag1', ['id1']);
        $redis->seedSet('tag2', []);
        $redis->seedSet('tag3', ['id1', 'id_untouched']);

        $backend = $this->createBackend($redis, ['useLua' => false]);

        $this->invokeMethod($backend, '_removeByMatchingAnyTags', [['tag1', 'tag2']]);

        $this->assertFalse($redis->hashExists('id1'));
        $this->assertSame(['id_untouched'], $redis->setMembers('tag3'));
    }

    /**
     * When Lua is enabled, the override must delegate straight to the upstream Lua-based
     * implementation instead of running its own PHP-side removal logic.
     */
    public function testRemoveByMatchingAnyTagsDelegatesToLuaWhenEnabled(): void
    {
        $redis = new RedisTestDouble();
        $redis->evalShaResult = true;

        $backend = $this->createBackend($redis, ['useLua' => true]);

        $this->invokeMethod($backend, '_removeByMatchingAnyTags', [['tag1']]);

        $calledCommands = array_column($redis->calls, 0);
        $this->assertContains('evalSha', $calledCommands);
        $this->assertNotContains('sUnion', $calledCommands);
    }

    /**
     * Build a Redis backend instance without running the (connection-requiring) constructor.
     *
     * @param RedisTestDouble $redis
     * @param array $properties Additional protected properties to override (name => value).
     * @return Redis
     */
    private function createBackend(RedisTestDouble $redis, array $properties = []): Redis
    {
        $rc = new ReflectionClass(Redis::class);
        $backend = $rc->newInstanceWithoutConstructor();

        (new ReflectionProperty(Redis::class, '_redis'))->setValue($backend, $redis);
        (new ReflectionProperty(Redis::class, '_removeChunkSize'))->setValue($backend, 10000);
        (new ReflectionProperty(Redis::class, '_notMatchingTags'))->setValue($backend, false);
        (new ReflectionProperty(Redis::class, '_useLua'))->setValue($backend, $properties['useLua'] ?? false);
        (new ReflectionProperty(Redis::class, '_sunionChunkSize'))->setValue($backend, 500);

        return $backend;
    }

    /**
     * Invoke a protected/private method via reflection.
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @return mixed
     */
    private function invokeMethod(object $object, string $method, array $args)
    {
        $reflectionMethod = new ReflectionMethod($object, $method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $args);
    }
}

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

    public function pipeline(...$_args): self
    {
        $this->inPipeline = true;
        $this->pending = [];

        return $this;
    }

    public function multi(...$_args): self
    {
        return $this;
    }

    public function hGet(string $key, string $_field)
    {
        $this->calls[] = ['hGet', $key];
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
            $union = array_merge($union, $this->sets[$this->stripPrefix($key, self::PREFIX_TAG_IDS)] ?? []);
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
