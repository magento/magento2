<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisTagAdapter;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * Two cache namespaces (id_prefix values) sharing one Redis database must not touch each other's id index.
 */
class RedisTagAdapterNamespaceTest extends TestCase
{
    private const DATABASE = 13;
    private const NAMESPACE_A = 'IT_NS_A_';
    private const NAMESPACE_B = 'IT_NS_B_';

    /**
     * @var \Redis
     */
    private \Redis $redis;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension is not loaded');
        }

        $this->redis = $this->connect();
        $this->redis->flushDB();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        if (isset($this->redis)) {
            $this->redis->flushDB();
        }

        parent::tearDown();
    }

    public function testGarbageCollectionKeepsIdsOfAnotherNamespace(): void
    {
        [$poolB, $tagsB] = $this->createAdapters(self::NAMESPACE_B);
        [, $tagsA] = $this->createAdapters(self::NAMESPACE_A);
        $this->save($poolB, $tagsB, 'b_live', []);
        $this->save($poolB, $tagsB, 'b_tagged', ['TAG']);

        $this->assertSame(0, $tagsA->garbageCollect());

        $this->assertEqualsCanonicalizing(['b_live', 'b_tagged'], $tagsB->getIdsNotMatchingTags([]));
        $this->assertSame(['b_live'], $tagsB->getIdsNotMatchingTags(['TAG']));
    }

    public function testClearAllIndicesKeepsIdsOfAnotherNamespace(): void
    {
        [$poolB, $tagsB] = $this->createAdapters(self::NAMESPACE_B);
        [$poolA, $tagsA] = $this->createAdapters(self::NAMESPACE_A);
        $this->save($poolA, $tagsA, 'a_live', []);
        $this->save($poolB, $tagsB, 'b_live', []);

        $tagsA->clearAllIndices();

        $this->assertSame([], $tagsA->getIdsNotMatchingTags([]));
        $this->assertSame(['b_live'], $tagsB->getIdsNotMatchingTags([]));
    }

    public function testIdsFromTheLegacyIdSetAreReadPerNamespaceBeforeGarbageCollection(): void
    {
        [$poolA, $tagsA, $poolB, $tagsB] = $this->createUpgradedState();

        $this->assertSame(['a_live'], $tagsA->getIdsNotMatchingTags(['TAG']));
        $this->assertEqualsCanonicalizing(['a_live', 'a_tagged'], $tagsA->getIdsNotMatchingTags([]));
        $this->assertSame(['b_live'], $tagsB->getIdsNotMatchingTags([]));
        $this->assertTrue($poolA->hasItem('a_live'));
        $this->assertTrue($poolB->hasItem('b_live'));
    }

    public function testGarbageCollectionAndFlushKeepTheLegacyIdSetForOtherNamespaces(): void
    {
        [, $tagsA, , $tagsB] = $this->createUpgradedState();
        $legacyIds = $this->redis->sMembers('cache:all_ids');

        $tagsA->garbageCollect();

        $this->assertSame(['a_tagged'], $this->redis->sMembers('cache:tags:' . self::NAMESPACE_A . 'TAG'));
        $this->assertSame(0, $this->redis->exists('cache:id_tags:' . self::NAMESPACE_A . 'a_gone'));

        $tagsA->clearAllIndices();

        $this->assertEqualsCanonicalizing($legacyIds, $this->redis->sMembers('cache:all_ids'));
        $this->assertSame(['b_live'], $tagsB->getIdsNotMatchingTags([]));
    }

    public function testCleanExpiredByTagKeepsEntriesWithoutExpiry(): void
    {
        [$pool, $tags] = $this->createAdapters(self::NAMESPACE_A, true);
        $this->save($pool, $tags, 'no_expiry', ['TAG']);
        $tagKey = 'cache:tags:' . self::NAMESPACE_A . 'TAG';
        $this->redis->sAdd($tagKey, 'gone');

        $this->assertSame(1, $tags->cleanExpiredByTag('TAG'));

        $this->assertTrue($pool->hasItem('no_expiry'));
        $this->assertSame(['no_expiry'], $this->redis->sMembers($tagKey));
    }

    /**
     * Recreate the index an earlier version left behind: ids of both namespaces in the unprefixed set.
     *
     * @return array{0: RedisAdapter, 1: RedisTagAdapter, 2: RedisAdapter, 3: RedisTagAdapter}
     */
    private function createUpgradedState(): array
    {
        [$poolA, $tagsA] = $this->createAdapters(self::NAMESPACE_A);
        [$poolB, $tagsB] = $this->createAdapters(self::NAMESPACE_B);
        foreach (['a_live', 'a_tagged'] as $id) {
            $poolA->save($poolA->getItem($id)->set('payload-' . $id));
        }
        $poolB->save($poolB->getItem('b_live')->set('payload-b_live'));
        $this->redis->sAdd('cache:all_ids', 'a_live', 'a_tagged', 'a_gone', 'b_live');
        $this->redis->sAdd('cache:tags:' . self::NAMESPACE_A . 'TAG', 'a_tagged', 'a_gone');
        $this->redis->sAdd('cache:id_tags:' . self::NAMESPACE_A . 'a_tagged', 'TAG');
        $this->redis->sAdd('cache:id_tags:' . self::NAMESPACE_A . 'a_gone', 'TAG');

        return [$poolA, $tagsA, $poolB, $tagsB];
    }

    /**
     * Build the Symfony pool and the tag adapter for a namespace, as the cache frontend factory does.
     *
     * @param string $namespace
     * @param bool $useLua
     * @return array{0: RedisAdapter, 1: RedisTagAdapter}
     */
    private function createAdapters(string $namespace, bool $useLua = false): array
    {
        $pool = new RedisAdapter($this->connect(), $namespace, 0);

        return [$pool, new RedisTagAdapter($pool, $namespace, $useLua)];
    }

    /**
     * Save a cache entry without expiry and index it with the given tags.
     *
     * @param RedisAdapter $pool
     * @param RedisTagAdapter $tags
     * @param string $id
     * @param string[] $tagList
     * @return void
     */
    private function save(RedisAdapter $pool, RedisTagAdapter $tags, string $id, array $tagList): void
    {
        $pool->save($pool->getItem($id)->set('payload-' . $id));
        $tags->onSave($id, $tagList);
    }

    /**
     * Connect to the test Redis database.
     *
     * @return \Redis
     */
    private function connect(): \Redis
    {
        $configuration = CacheConfigurationProvider::provide()['symfony-redis'][1];
        $redis = new \Redis();
        try {
            $redis->connect((string)$configuration['backend_options']['server'], 6379);
        } catch (\RedisException $e) {
            $this->markTestSkipped('Redis server is not available: ' . $e->getMessage());
        }
        $redis->select(self::DATABASE);

        return $redis;
    }
}
