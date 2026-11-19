<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Framework\Cache\Frontend\Adapter\OptimizedPredisClient;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisTagAdapter;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * Two cache namespaces (id_prefix values) sharing one Redis database must not touch each other's id index.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RedisTagAdapterNamespaceTest extends TestCase
{
    private const DATABASE = 13;
    private const NAMESPACE_A = 'IT_NS_A_';
    private const NAMESPACE_B = 'IT_NS_B_';
    private const LEGACY_IDS = ['a_live', 'a_tagged', 'a_gone', 'b_live', 'b_tagged'];

    /**
     * @var \Redis
     */
    private \Redis $redis;

    /**
     * @var string
     */
    private string $client = 'phpredis';

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

    /**
     * @return array<string, array{string}>
     */
    public static function clientProvider(): array
    {
        return ['phpredis' => ['phpredis'], 'predis' => ['predis']];
    }

    #[DataProvider('clientProvider')]
    public function testGarbageCollectionKeepsIdsOfAnotherNamespace(string $client): void
    {
        $this->client = $client;
        [$poolB, $tagsB] = $this->createAdapters(self::NAMESPACE_B);
        [, $tagsA] = $this->createAdapters(self::NAMESPACE_A);
        $this->save($poolB, $tagsB, 'b_live', []);
        $this->save($poolB, $tagsB, 'b_tagged', ['TAG']);

        $this->assertSame(0, $tagsA->garbageCollect());

        $this->assertEqualsCanonicalizing(['b_live', 'b_tagged'], $tagsB->getIdsNotMatchingTags([]));
        $this->assertSame(['b_live'], $tagsB->getIdsNotMatchingTags(['TAG']));
    }

    #[DataProvider('clientProvider')]
    public function testClearAllIndicesKeepsIdsOfAnotherNamespace(string $client): void
    {
        $this->client = $client;
        [$poolB, $tagsB] = $this->createAdapters(self::NAMESPACE_B);
        [$poolA, $tagsA] = $this->createAdapters(self::NAMESPACE_A);
        $this->save($poolA, $tagsA, 'a_live', []);
        $this->save($poolB, $tagsB, 'b_live', []);

        $tagsA->clearAllIndices();

        $this->assertSame([], $tagsA->getIdsNotMatchingTags([]));
        $this->assertSame(['b_live'], $tagsB->getIdsNotMatchingTags([]));
    }

    #[DataProvider('clientProvider')]
    public function testIdsFromTheLegacyIdSetAreReadPerNamespaceBeforeGarbageCollection(string $client): void
    {
        $this->client = $client;
        [$tagsA, $tagsB] = $this->createUpgradedState();

        $this->assertSame(['a_live'], $tagsA->getIdsNotMatchingTags(['TAG']));
        $this->assertEqualsCanonicalizing(['a_live', 'a_tagged'], $tagsA->getIdsNotMatchingTags([]));
        $this->assertSame(['b_live'], $tagsB->getIdsNotMatchingTags(['TAG']));
        $this->assertEqualsCanonicalizing(['b_live', 'b_tagged'], $tagsB->getIdsNotMatchingTags([]));
        $this->assertEqualsCanonicalizing(self::LEGACY_IDS, $this->redis->sMembers('cache:all_ids'));
    }

    #[DataProvider('clientProvider')]
    public function testGarbageCollectionAndFlushKeepTheLegacyIdSetForOtherNamespaces(string $client): void
    {
        $this->client = $client;
        [$tagsA, $tagsB] = $this->createUpgradedState();

        $tagsA->garbageCollect();

        $this->assertSame(['a_tagged'], $this->redis->sMembers('cache:tags:' . self::NAMESPACE_A . 'TAG'));
        $this->assertSame(0, $this->redis->exists('cache:id_tags:' . self::NAMESPACE_A . 'a_gone'));

        $tagsA->clearAllIndices();

        $this->assertEqualsCanonicalizing(self::LEGACY_IDS, $this->redis->sMembers('cache:all_ids'));
        $this->assertSame(['b_live'], $tagsB->getIdsNotMatchingTags(['TAG']));
        $this->assertSame(['b_tagged'], $tagsB->getIdsMatchingTags(['TAG']));
    }

    #[DataProvider('clientProvider')]
    public function testLuaFlushKeepsTheLegacyIdSetAndEntriesOfAnotherNamespace(string $client): void
    {
        $this->client = $client;
        [$tagsA, $tagsB] = $this->createUpgradedState(true);

        $tagsA->clearAllIndices();

        $this->assertSame(0, $this->redis->exists('cache:tags:' . self::NAMESPACE_A . 'TAG'));
        $this->assertEqualsCanonicalizing(self::LEGACY_IDS, $this->redis->sMembers('cache:all_ids'));
        $this->assertSame(['b_live'], $tagsB->getIdsNotMatchingTags(['TAG']));
        $this->assertEqualsCanonicalizing(['b_live', 'b_tagged'], $tagsB->getIdsNotMatchingTags([]));
        $this->assertSame(['b_tagged'], $tagsB->getIdsMatchingTags(['TAG']));
    }

    #[DataProvider('clientProvider')]
    public function testLegacyIdSetIsReadOnlyOncePerNamespace(string $client): void
    {
        $this->client = $client;
        [$tagsA] = $this->createUpgradedState();
        $tagsA->getIdsNotMatchingTags([]);
        $this->assertSame(1, $this->redis->exists('cache:all_ids_migrated:' . self::NAMESPACE_A));

        $this->redis->sRem('cache:all_ids:' . self::NAMESPACE_A, 'a_live');
        [, $freshTagsA] = $this->createAdapters(self::NAMESPACE_A);
        $freshTagsA->garbageCollect();

        $this->assertSame(['a_tagged'], $freshTagsA->getIdsNotMatchingTags([]));

        $freshTagsA->clearAllIndices();

        $this->assertSame(1, $this->redis->exists('cache:all_ids_migrated:' . self::NAMESPACE_A));
        $this->assertSame(0, $this->redis->exists('cache:all_ids_migrated:' . self::NAMESPACE_B));
    }

    #[DataProvider('clientProvider')]
    public function testCleanExpiredByTagKeepsEntriesWithoutExpiry(string $client): void
    {
        $this->client = $client;
        [$pool, $tags] = $this->createAdapters(self::NAMESPACE_A, true);
        $this->save($pool, $tags, 'no_expiry', ['TAG']);
        $tagKey = 'cache:tags:' . self::NAMESPACE_A . 'TAG';
        $this->redis->sAdd($tagKey, 'gone');

        $this->assertSame(1, $tags->cleanExpiredByTag('TAG'));

        $this->assertTrue($pool->hasItem('no_expiry'));
        $this->assertSame(['no_expiry'], $this->redis->sMembers($tagKey));
    }

    /**
     * Seed an index whose id set has no namespace and holds the ids of both namespaces.
     *
     * Namespace A: a_live (no tags), a_tagged (TAG), a_gone (TAG, data expired).
     * Namespace B: b_live (no tags), b_tagged (TAG).
     *
     * @param bool $useLua
     * @return array{0: RedisTagAdapter, 1: RedisTagAdapter}
     */
    private function createUpgradedState(bool $useLua = false): array
    {
        $tagged = [self::NAMESPACE_A => ['a_tagged', 'a_gone'], self::NAMESPACE_B => ['b_tagged']];
        $withData = [self::NAMESPACE_A => ['a_live', 'a_tagged'], self::NAMESPACE_B => ['b_live', 'b_tagged']];
        $adapters = [];
        foreach ([self::NAMESPACE_A, self::NAMESPACE_B] as $namespace) {
            [$pool, $tags] = $this->createAdapters($namespace, $useLua);
            foreach ($withData[$namespace] as $id) {
                $pool->save($pool->getItem($id)->set('payload-' . $id));
            }
            foreach ($tagged[$namespace] as $id) {
                $this->redis->sAdd('cache:tags:' . $namespace . 'TAG', $id);
                $this->redis->sAdd('cache:id_tags:' . $namespace . $id, 'TAG');
            }
            $adapters[] = $tags;
        }
        $this->redis->sAdd('cache:all_ids', ...self::LEGACY_IDS);

        return $adapters;
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
        $connection = $this->client === 'predis'
            ? new OptimizedPredisClient(
                ['host' => $this->getServer(), 'port' => 6379, 'database' => self::DATABASE],
                ['exceptions' => false]
            )
            : $this->connect();
        $pool = new RedisAdapter($connection, $namespace, 0);

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
        $redis = new \Redis();
        try {
            $redis->connect($this->getServer(), 6379);
        } catch (\RedisException $e) {
            $this->markTestSkipped('Redis server is not available: ' . $e->getMessage());
        }
        $redis->select(self::DATABASE);

        return $redis;
    }

    /**
     * Get the Redis host the integration cache configuration uses.
     *
     * @return string
     */
    private function getServer(): string
    {
        return (string)CacheConfigurationProvider::provide()['symfony-redis'][1]['backend_options']['server'];
    }
}
