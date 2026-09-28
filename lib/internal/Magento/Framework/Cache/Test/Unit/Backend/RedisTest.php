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
 * Unit tests for: stale tag-index cleanup in the legacy Redis cache backend.
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
            if ($errno === E_USER_WARNING) {
                $warnings[] = $errstr;
            }

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
    private function invokeMethod(object $object, string $method, array $args): mixed
    {
        $reflectionMethod = new ReflectionMethod($object, $method);

        return $reflectionMethod->invokeArgs($object, $args);
    }
}
