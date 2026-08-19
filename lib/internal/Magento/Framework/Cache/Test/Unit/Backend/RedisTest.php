<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Backend;

use Magento\Framework\Cache\Backend\Redis;
use PHPUnit\Framework\TestCase;

class RedisTest extends TestCase
{
    /**
     * Builds a Redis backend with its protected $_redis client replaced by a fake that records
     * pipeline usage instead of talking to a real server.
     *
     * @param string[] $preloadKeys
     * @return array{0: Redis, 1: RedisTestFakeClient}
     */
    private function createBackend(array $preloadKeys): array
    {
        $backend = new Redis(['server' => 'localhost', 'preload_keys' => $preloadKeys]);
        $client = new RedisTestFakeClient();

        $bind = \Closure::bind(function ($instance, $redisClient) {
            $instance->_redis = $redisClient;
        }, null, Redis::class);
        $bind($backend, $client);

        return [$backend, $client];
    }

    /**
     * A batch in which every preload key misses must not cause the pipeline to re-fire on the
     * next load(): the guard must track "already ran", not "found something".
     *
     * @return void
     */
    public function testPreloadPipelineDoesNotRefireAfterTotalMiss(): void
    {
        [$backend, $client] = $this->createBackend(['a', 'b']);
        $client->queueExecResult([false, false]);
        $client->setDirectResult('a', 'fetched-a');

        $backend->load('a');
        $backend->load('a');

        $this->assertSame(
            1,
            $client->pipelineCount,
            'The preload pipeline must run at most once, even when every key misses.'
        );
    }

    /**
     * A preloaded hit is served from the batch and does not trigger a second pipeline on a later
     * load() of a different id from the same batch.
     *
     * @return void
     */
    public function testPreloadedHitIsReusedWithoutRefiring(): void
    {
        [$backend, $client] = $this->createBackend(['a', 'b']);
        $client->queueExecResult(['payload-a', false]);
        $client->setDirectResult('b', 'fetched-b');

        $this->assertSame('payload-a', $backend->load('a'));
        $this->assertSame('fetched-b', $backend->load('b'));
        $this->assertSame(1, $client->pipelineCount);
    }

    /**
     * save() must drop the preloaded snapshot for the id it writes, even when the underlying
     * write itself fails, so the next load() re-reads Redis instead of serving stale data.
     *
     * @return void
     */
    public function testSaveDropsStalePreloadedValue(): void
    {
        [$backend, $client] = $this->createBackend(['a']);
        $client->queueExecResult(['stale-a']);
        $client->setDirectResult('a', 'fresh-a');

        $this->assertSame('stale-a', $backend->load('a'));

        // The fake client has no hMSet/multi support, so parent::save() throws; save() itself
        // catches that and returns false - the snapshot must still have been dropped beforehand.
        $this->assertFalse($backend->save('new-a', 'a'));

        $this->assertSame(
            'fresh-a',
            $backend->load('a'),
            'load() must not keep serving the pre-write value after save() for the same id.'
        );
        $this->assertSame(1, $client->pipelineCount);
    }

    /**
     * remove() must drop the preloaded snapshot for the id it removes, so the next load()
     * reports the removal instead of the pre-removal value.
     *
     * @return void
     */
    public function testRemoveDropsStalePreloadedValue(): void
    {
        [$backend, $client] = $this->createBackend(['a']);
        $client->queueExecResult(['stale-a']);
        $client->setDirectResult('a', false);

        $this->assertSame('stale-a', $backend->load('a'));

        $backend->remove('a');

        $this->assertFalse(
            $backend->load('a'),
            'load() must not resurrect a removed id from the stale preloaded snapshot.'
        );
    }
}

/**
 * Minimal stand-in for Credis_Client covering only what Redis::load()/save()/remove() call
 * directly: a pipelined batch of hGet() calls flushed by exec(), and a standalone hGet() for the
 * non-preloaded path. Anything save()/remove() need beyond that (hMSet, multi, del, ...) is left
 * unimplemented on purpose, so those calls surface as an Error that the caller's own try/catch
 * around parent::save()/parent::remove() is expected to swallow.
 */
class RedisTestFakeClient
{
    public int $pipelineCount = 0;

    /** @var array<int, array<int, mixed>> */
    private array $queuedExecResults = [];

    /** @var array<string, mixed> */
    private array $directResults = [];

    private bool $inPipeline = false;

    public function queueExecResult(array $result): void
    {
        $this->queuedExecResults[] = $result;
    }

    public function setDirectResult(string $id, $value): void
    {
        $this->directResults[$id] = $value;
    }

    public function pipeline(): self
    {
        $this->pipelineCount++;
        $this->inPipeline = true;

        return $this;
    }

    public function hGet(string $key, string $field)
    {
        if ($this->inPipeline) {
            // Queued: the actual value is returned positionally by exec(), not here.
            return $this;
        }

        $id = $this->stripKeyPrefix($key);

        return $this->directResults[$id] ?? false;
    }

    public function exec(): array
    {
        $this->inPipeline = false;

        return array_shift($this->queuedExecResults) ?? [];
    }

    private function stripKeyPrefix(string $key): string
    {
        return substr($key, strlen(Redis::PREFIX_KEY));
    }
}
