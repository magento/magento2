<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\SymfonyAdapters;

use Magento\Framework\Cache\Frontend\Adapter\OptimizedPredisClient;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisTagAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

/**
 * Unit tests for RedisTagAdapter::getIdsMatchingTags()
 *
 * Uses anonymous-class stubs injected via reflection (PHP 8.1+ allows setValue() on
 * private properties without setAccessible).
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedisTagAdapterTest extends TestCase
{
    /**
     * Empty tags array returns empty result without any Redis calls.
     */
    public function testGetIdsMatchingTagsEmptyTagsReturnsEmpty(): void
    {
        [$redis] = $this->makeFakeRedis();
        $adapter = $this->createAdapter($redis);

        $this->assertSame([], $adapter->getIdsMatchingTags([]));
    }

    /**
     * Single tag uses a direct sMembers call, not a pipeline.
     */
    public function testGetIdsMatchingTagsSingleTagReturnsMembersDirectly(): void
    {
        $expectedIds = ['item_1', 'item_2'];
        [$redis, $state] = $this->makeFakeRedis();
        $state->sMembersResult = $expectedIds;

        $adapter = $this->createAdapter($redis);
        $result  = $adapter->getIdsMatchingTags(['tagA']);

        $this->assertSame($expectedIds, $result);
        $this->assertTrue($state->sMembersCalled, 'sMembers must be called for a single tag');
        $this->assertFalse($state->sinterCalled, 'sinter must not be called for a single tag');
    }

    /**
     * When the smallest set size exceeds SINTER_SAFE_SIZE (500), sinter must NOT be called.
     * PHP-side array_intersect is used instead to avoid blocking Valkey.
     *
     * This test FAILS on the unfixed code (bare sinter always used) and
     * PASSES after the fix (size-adaptive strategy added).
     */
    public function testGetIdsMatchingTagsLargeSetsDoNotCallSinter(): void
    {
        // Two sets both larger than SINTER_SAFE_SIZE = 500
        [$redis, $state] = $this->makeFakeRedis(
            scardResults: [600, 800],
            sMembersPipelineResults: [['id1', 'id2', 'id3'], ['id2', 'id3', 'id4']]
        );

        $adapter = $this->createAdapter($redis);
        $result  = $adapter->getIdsMatchingTags(['tagA', 'tagB']);

        $this->assertFalse($state->sinterCalled, 'sinter must NOT be called when all sets exceed SINTER_SAFE_SIZE');
        sort($result);
        $this->assertSame(['id2', 'id3'], $result, 'Result must be the PHP intersection of the two sets');
    }

    /**
     * When the smallest set is at or below SINTER_SAFE_SIZE (500), sinter IS used.
     * Regression guard to ensure small sets still take the efficient SINTER path.
     */
    public function testGetIdsMatchingTagsSmallSetsCallSinter(): void
    {
        $sinterResult = ['id_common'];
        [$redis, $state] = $this->makeFakeRedis(
            scardResults: [3, 5],
            sinterResult: $sinterResult
        );

        $adapter = $this->createAdapter($redis);
        $result  = $adapter->getIdsMatchingTags(['tagA', 'tagB']);

        $this->assertTrue($state->sinterCalled, 'sinter must be called when the smallest set is <= SINTER_SAFE_SIZE');
        $this->assertSame($sinterResult, $result);
    }

    /**
     * If any tag set is empty (scard returns 0), the intersection is empty without calling sinter.
     */
    public function testGetIdsMatchingTagsShortCircuitsOnEmptySet(): void
    {
        [$redis, $state] = $this->makeFakeRedis(scardResults: [0, 500]);

        $adapter = $this->createAdapter($redis);
        $result  = $adapter->getIdsMatchingTags(['tagA', 'tagB']);

        $this->assertSame([], $result);
        $this->assertFalse($state->sinterCalled, 'sinter must not be called when any set is empty');
    }

    /**
     * Create a RedisTagAdapter with an injected fake Redis client.
     *
     * ReflectionProperty::setValue() works on private properties without setAccessible()
     * in PHP 8.1+ (the method became a no-op).
     */
    private function createAdapter(object $redisClient): RedisTagAdapter
    {
        $rc      = new ReflectionClass(RedisTagAdapter::class);
        $adapter = $rc->newInstanceWithoutConstructor();

        (new ReflectionProperty(RedisTagAdapter::class, 'redis'))->setValue($adapter, $redisClient);
        (new ReflectionProperty(RedisTagAdapter::class, 'namespace'))->setValue($adapter, '');
        (new ReflectionProperty(RedisTagAdapter::class, 'cachePool'))->setValue(
            $adapter,
            $this->createMock(CacheItemPoolInterface::class)
        );
        (new ReflectionProperty(RedisTagAdapter::class, 'useLua'))->setValue($adapter, false);
        (new ReflectionProperty(RedisTagAdapter::class, 'useLuaOnGc'))->setValue($adapter, false);
        (new ReflectionProperty(RedisTagAdapter::class, 'luaHelper'))->setValue($adapter, null);

        return $adapter;
    }

    /**
     * Build a fake Predis-compatible Redis stub and a shared state object.
     *
     * The stub extends OptimizedPredisClient so that RedisTagAdapter::isPredisClient()
     * returns true, which routes createPipeline() through the Predis pipeline path.
     * No actual Redis connection is made.
     *
     * @param array $scardResults            Values returned by the pipeline's first execute() call.
     * @param array $sMembersPipelineResults Values returned by the pipeline's second execute() call.
     * @param array $sinterResult            Value returned by a direct sinter() call.
     * @return array{0: object, 1: stdClass}  [fakeRedis, state]
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function makeFakeRedis(
        array $scardResults = [],
        array $sMembersPipelineResults = [],
        array $sinterResult = []
    ): array {
        $state = new stdClass();
        $state->sinterCalled          = false;
        $state->sinterResult          = $sinterResult;
        $state->sMembersCalled        = false;
        $state->sMembersResult        = [];

        // Pipeline stub: scard/sMembers calls are queued via __call; execute() returns pre-set results.
        $pipeline = new class {
            /** @var array */
            public array $execQueue = [];

            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            public function __call($method, $args): static
            {
                return $this;
            }

            public function execute(): array
            {
                return array_shift($this->execQueue) ?? [];
            }
        };

        $pipeline->execQueue = array_filter(
            [$scardResults ?: null, $sMembersPipelineResults ?: null],
            fn($v) => $v !== null
        );

        // Redis stub: extends OptimizedPredisClient so isPredisClient() returns true.
        // Overrides pipeline() and __call() to avoid real Redis connection.
        // @SuppressWarnings(PHPMD.UnusedLocalVariable) — $pipe and $st are assigned to $this->*
        $redis = new class ($pipeline, $state) extends OptimizedPredisClient {
            /** @var object */
            private object $pipe;

            /** @var stdClass */
            private stdClass $st;

            public function __construct(object $pipe, stdClass $st)
            {
                $this->pipe = $pipe;
                $this->st   = $st;
                // Skip parent::__construct() — no Redis connection needed for unit tests.
            }

            public function pipeline(...$_arguments)
            {
                return $this->pipe;
            }

            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            public function __call($commandID, $_arguments)
            {
                $lower = strtolower((string)$commandID);

                if ($lower === 'sinter') {
                    $this->st->sinterCalled = true;
                    return $this->st->sinterResult;
                }

                if ($lower === 'smembers') {
                    $this->st->sMembersCalled = true;
                    return $this->st->sMembersResult;
                }

                return null;
            }
        };

        return [$redis, $state];
    }
}
