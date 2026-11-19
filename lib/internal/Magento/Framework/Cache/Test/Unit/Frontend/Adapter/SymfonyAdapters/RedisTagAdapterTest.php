<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\SymfonyAdapters;

use Magento\Framework\Cache\Frontend\Adapter\OptimizedPredisClient;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisLuaHelper;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisTagAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

/**
 * Unit tests for RedisTagAdapter
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

    public function testGetIdsNotMatchingTagsDiffsTheNamespaceAllIdsSet(): void
    {
        $redis = $this->makeRecordingRedis(['sdiff' => ['id_1'], 'sscan' => ['0', []]]);

        $result = $this->createAdapter($redis, 'ns_')->getIdsNotMatchingTags(['tagA']);

        $this->assertSame(['id_1'], $result);
        $this->assertSame([['cache:all_ids:ns_', 'cache:tags:ns_tagA']], $redis->argsOf('sdiff'));
    }

    public function testGetIdsNotMatchingTagsWithoutTagsReadsTheNamespaceAllIdsSet(): void
    {
        $redis = $this->makeRecordingRedis(['smembers' => ['id_1'], 'sscan' => ['0', []]]);

        $result = $this->createAdapter($redis, 'ns_')->getIdsNotMatchingTags([]);

        $this->assertSame(['id_1'], $result);
        $this->assertSame([['cache:all_ids:ns_']], $redis->argsOf('smembers'));
    }

    public function testGetIdsNotMatchingTagsCopiesLegacyIdsWithDataInTheNamespace(): void
    {
        $redis = $this->makeRecordingRedis([
            'sscan' => fn (string $key) => $key === 'cache:all_ids' ? ['0', ['mine', 'other']] : ['0', []],
            'smembers' => ['mine'],
        ]);
        $redis->pipe->execQueue = [[1, 0], [1]];

        $this->assertSame(['mine'], $this->createAdapter($redis, 'ns_')->getIdsNotMatchingTags([]));
        $this->assertSame(['ns_:mine', 'ns_:other'], array_merge(...$redis->argsOf('pipe:exists')));
        $this->assertSame([['cache:all_ids:ns_', 'mine']], $redis->argsOf('pipe:sadd'));
        $this->assertLegacySetUntouched($redis);
    }

    public function testOnSaveWithTagsPassesTheNamespaceAllIdsSetToLua(): void
    {
        $redis = $this->makeRecordingRedis();

        $this->createAdapter($redis, 'ns_')->onSave('id_1', ['tagA', 'tagB']);

        $this->assertSame(
            [
                'sha',
                0,
                'ns_:id_1',
                'id_1',
                'cache:all_ids:ns_',
                'cache:tags:',
                'ns_',
                'cache:id_tags:ns_id_1',
                'tagA',
                'tagB',
            ],
            $redis->argsOf('evalsha')[0]
        );
    }

    public function testOnSaveWithoutTagsRegistersInTheNamespaceAllIdsSet(): void
    {
        $redis = $this->makeRecordingRedis(['exists' => 1]);

        $this->createAdapter($redis, 'ns_')->onSave('id_1', []);

        $this->assertSame([['exists', ['ns_:id_1']], ['sadd', ['cache:all_ids:ns_', 'id_1']]], $redis->calls);
    }

    public function testDeleteByIdsPrunesTheNamespaceAllIdsSet(): void
    {
        $redis = $this->makeRecordingRedis();
        $pool = $this->createStub(CacheItemPoolInterface::class);
        $pool->method('deleteItems')->willReturn(true);
        $adapter = $this->createAdapter($redis, 'ns_');
        (new ReflectionProperty(RedisTagAdapter::class, 'cachePool'))->setValue($adapter, $pool);

        $this->assertTrue($adapter->deleteByIds(['id_1', 'id_2']));
        $this->assertSame(
            ['sha', 0, 'cache:tags:', 'ns_', 'ns_:', 'cache:all_ids:ns_', 'id_1', 'id_2'],
            $redis->argsOf('evalsha')[0]
        );
    }

    public function testDeleteByIdsPipelinedFallbackRemovesFromTheNamespaceAllIdsSet(): void
    {
        $redis = $this->makeRecordingRedis(['script' => new \RuntimeException('NOSCRIPT')]);
        $redis->pipe->execQueue = [[['tagA']], []];
        $adapter = $this->createAdapter($redis, 'ns_');

        $adapter->deleteByIds(['id_1']);

        $this->assertContains(['pipe:srem', ['cache:all_ids:ns_', 'id_1']], $redis->calls);
        $this->assertContains(['pipe:srem', ['cache:tags:ns_tagA', 'id_1']], $redis->calls);
    }

    public function testCleanMatchingAnyTagsLuaPassesTheNamespaceAllIdsSet(): void
    {
        $redis = $this->makeRecordingRedis(['eval' => 1, 'evalsha' => 2]);
        $adapter = $this->createAdapter($redis, 'ns_');
        (new ReflectionProperty(RedisTagAdapter::class, 'useLua'))->setValue($adapter, true);
        (new ReflectionProperty(RedisTagAdapter::class, 'luaHelper'))->setValue(
            $adapter,
            new RedisLuaHelper($redis, true)
        );

        $this->assertTrue($adapter->cleanMatchingAnyTags(['tagA', 'tagB']));
        $this->assertTrue($adapter->cleanMatchingAnyTagsWithScope(['tagA'], 'scope'));

        $evalShaCalls = $redis->argsOf('evalsha');
        $this->assertSame(
            ['sha', 2, 'tagA', 'tagB', 'cache:tags:', 'ns_', 100, 'ns_:', 'cache:all_ids:ns_'],
            $evalShaCalls[0]
        );
        $this->assertSame(
            ['sha', 1, 'tagA', 'cache:tags:', 'ns_', 'scope', 'ns_:', 'cache:all_ids:ns_'],
            $evalShaCalls[1]
        );
    }

    public function testClearAllIndicesDeletesOnlyTheNamespaceAllIdsSet(): void
    {
        $redis = $this->makeRecordingRedis(['scan' => ['0', []]]);

        $this->createAdapter($redis, 'ns_')->clearAllIndices();

        $this->assertSame([['cache:all_ids:ns_']], $redis->argsOf('del'));
    }

    public function testGarbageCollectScansTheNamespaceAllIdsSet(): void
    {
        $redis = $this->makeRecordingRedis([
            'sscan' => fn (string $key) => $key === 'cache:all_ids:ns_' ? ['0', ['gone']] : ['0', []],
        ]);
        $redis->pipe->execQueue = [[0]];

        $this->assertSame(1, $this->createAdapter($redis, 'ns_')->garbageCollect());
        $this->assertSame(['ns_:gone'], $redis->argsOf('pipe:exists')[0]);
        $this->assertSame(
            ['sha', 0, 'cache:tags:', 'ns_', 'ns_:', 'cache:all_ids:ns_', 'gone'],
            $redis->argsOf('evalsha')[0]
        );
    }

    public function testGarbageCollectMigratesLegacyIdsButKeepsTheLegacySet(): void
    {
        $redis = $this->makeRecordingRedis([
            'sscan' => fn (string $key) => $key === 'cache:all_ids' ? ['0', ['live', 'gone']] : ['0', []],
        ]);
        $redis->pipe->execQueue = [[1, 0], [1]];

        $this->assertSame(0, $this->createAdapter($redis, 'ns_')->garbageCollect());
        $this->assertSame(['ns_:live', 'ns_:gone'], array_merge(...$redis->argsOf('pipe:exists')));
        $this->assertSame([['cache:all_ids:ns_', 'live']], $redis->argsOf('pipe:sadd'));
        $this->assertSame(
            ['sha', 0, 'cache:tags:', 'ns_', 'ns_:', 'cache:all_ids:ns_', 'gone'],
            $redis->argsOf('evalsha')[0]
        );
        $this->assertSame([['cache:all_ids_migrated:ns_', '1']], $redis->argsOf('set'));
        $this->assertLegacySetUntouched($redis);
    }

    public function testLegacyIdsAreMigratedOncePerAdapterInstance(): void
    {
        $redis = $this->makeRecordingRedis(['sscan' => ['0', []], 'smembers' => []]);
        $adapter = $this->createAdapter($redis, 'ns_');

        $adapter->getIdsNotMatchingTags([]);
        $adapter->getIdsNotMatchingTags([]);
        $adapter->garbageCollect();

        $this->assertSame([['cache:all_ids_migrated:ns_']], $redis->argsOf('exists'));
        $legacyScans = array_filter($redis->argsOf('sscan'), fn (array $args) => $args[0] === 'cache:all_ids');
        $this->assertCount(1, $legacyScans);
        $this->assertSame([['cache:all_ids_migrated:ns_', '1']], $redis->argsOf('set'));
    }

    public function testLegacySetIsNotReadWhenTheNamespaceIsMarkedAsMigrated(): void
    {
        $redis = $this->makeRecordingRedis(['exists' => 1, 'sscan' => ['0', []], 'smembers' => ['id_1']]);

        $this->assertSame(['id_1'], $this->createAdapter($redis, 'ns_')->getIdsNotMatchingTags([]));
        $this->assertSame([], $redis->argsOf('sscan'));
        $this->assertSame([], $redis->argsOf('set'));
    }

    /**
     * Assert that no write command addressed the legacy all_ids set.
     *
     * @param OptimizedPredisClient $redis
     * @return void
     */
    private function assertLegacySetUntouched(OptimizedPredisClient $redis): void
    {
        foreach ($redis->calls as [$command, $arguments]) {
            if (in_array($command, ['sscan', 'pipe:exists', 'evalsha'], true)) {
                continue;
            }
            $this->assertNotContains('cache:all_ids', (array)($arguments[0] ?? null), $command);
        }
    }

    /**
     * Create a RedisTagAdapter with an injected fake Redis client.
     *
     * ReflectionProperty::setValue() works on private properties without setAccessible()
     * in PHP 8.1+ (the method became a no-op).
     */
    private function createAdapter(object $redisClient, string $namespace = ''): RedisTagAdapter
    {
        $rc      = new ReflectionClass(RedisTagAdapter::class);
        $adapter = $rc->newInstanceWithoutConstructor();

        (new ReflectionProperty(RedisTagAdapter::class, 'redis'))->setValue($adapter, $redisClient);
        (new ReflectionProperty(RedisTagAdapter::class, 'namespace'))->setValue($adapter, $namespace);
        (new ReflectionProperty(RedisTagAdapter::class, 'cachePool'))->setValue(
            $adapter,
            $this->createStub(CacheItemPoolInterface::class)
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

    /**
     * Build a Predis-compatible fake that records every command, including pipelined ones ("pipe:<cmd>").
     *
     * A return value may be a callable receiving the command arguments, or a Throwable to throw.
     * SCRIPT LOAD returns "sha" unless overridden.
     *
     * @param array $returns Return value per lower-case command name
     * @return OptimizedPredisClient
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function makeRecordingRedis(array $returns = []): OptimizedPredisClient
    {
        $returns += ['script' => 'sha'];

        $recorder = new class {
            /** @var array */
            public array $calls = [];
        };

        $pipeline = new class ($recorder) {
            /** @var array */
            public array $execQueue = [];

            /** @var object */
            private object $recorder;

            public function __construct(object $recorder)
            {
                $this->recorder = $recorder;
            }

            public function __call($method, $args): static
            {
                $this->recorder->calls[] = ['pipe:' . strtolower((string)$method), $args];
                return $this;
            }

            public function execute(): array
            {
                return array_shift($this->execQueue) ?? [];
            }
        };

        return new class ($pipeline, $recorder, $returns) extends OptimizedPredisClient {
            /** @var object */
            public object $pipe;

            /** @var array */
            public array $calls = [];

            /** @var object */
            private object $recorder;

            /** @var array */
            private array $returns;

            public function __construct(object $pipe, object $recorder, array $returns)
            {
                $this->pipe = $pipe;
                $this->recorder = $recorder;
                $this->returns = $returns;
                $this->calls = &$recorder->calls;
            }

            public function pipeline(...$_arguments)
            {
                return $this->pipe;
            }

            public function del($keys)
            {
                return $this->__call('del', [$keys]);
            }

            public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
            {
                return $this->__call('set', [$key, $value]);
            }

            public function __call($commandID, $arguments)
            {
                $command = strtolower((string)$commandID);
                if ($command !== 'script' && $command !== 'eval') {
                    $this->recorder->calls[] = [$command, $arguments];
                }
                $result = $this->returns[$command] ?? null;
                if ($result instanceof \Throwable) {
                    throw $result;
                }

                return is_callable($result) ? $result(...$arguments) : $result;
            }

            public function argsOf(string $command): array
            {
                $args = [];
                foreach ($this->recorder->calls as [$name, $arguments]) {
                    if ($name === $command) {
                        $args[] = $arguments;
                    }
                }

                return $args;
            }
        };
    }
}
