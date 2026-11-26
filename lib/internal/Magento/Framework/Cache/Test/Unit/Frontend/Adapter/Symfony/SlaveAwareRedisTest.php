<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\Frontend\Adapter\Symfony\SlaveAwareRedis;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for SlaveAwareRedis read routing.
 *
 * Requires the phpredis extension (SlaveAwareRedis extends \Redis); skipped otherwise. Only the
 * replica-served paths are asserted here — the master fallback needs a live connection and is
 * covered by integration tests.
 */
class SlaveAwareRedisTest extends TestCase
{
    /**
     * @var SlaveAwareRedis
     */
    private SlaveAwareRedis $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('phpredis extension is not loaded.');
        }

        $this->model = new SlaveAwareRedis();
    }

    /**
     * With master_write_only enabled, get() is served by the replica on a hit and never touches
     * the master.
     */
    public function testGetIsServedByReplicaOnHit(): void
    {
        $slave = $this->createSlaveReturningGet('cache_key', 'replica-value');
        $this->model->setSlaves([$slave]);
        $this->model->setMasterWriteOnly(true);

        $this->assertSame('replica-value', $this->model->get('cache_key'));
    }

    /**
     * A replica miss stays a miss when retry_reads_on_master is disabled (legacy default).
     */
    public function testGetReturnsFalseOnReplicaMissWithoutRetry(): void
    {
        $slave = $this->createSlaveReturningGet('cache_key', false);
        $this->model->setSlaves([$slave]);
        $this->model->setMasterWriteOnly(true);
        $this->model->setRetryReadsOnMaster(false);

        $this->assertFalse($this->model->get('cache_key'));
    }

    /**
     * With master_write_only enabled, mget() is served by the replica.
     */
    public function testMgetIsServedByReplica(): void
    {
        $slave = $this->createMock(\Redis::class);
        $slave->method('mget')->with(['a', 'b'])->willReturn(['va', 'vb']);
        $this->model->setSlaves([$slave]);
        $this->model->setMasterWriteOnly(true);

        $this->assertSame(['va', 'vb'], $this->model->mget(['a', 'b']));
    }

    /**
     * Non-\Redis entries passed to setSlaves() are ignored, so a lone invalid replica leaves no
     * replicas and reads are not routed to it.
     */
    public function testSetSlavesFiltersOutNonRedisEntries(): void
    {
        $validSlave = $this->createSlaveReturningGet('cache_key', 'replica-value');
        // The stdClass entry must be filtered out; only the valid replica should ever serve reads.
        $this->model->setSlaves([new \stdClass(), $validSlave]);
        $this->model->setMasterWriteOnly(true);

        $this->assertSame('replica-value', $this->model->get('cache_key'));
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return \Redis|MockObject
     */
    private function createSlaveReturningGet(string $key, $value)
    {
        $slave = $this->createMock(\Redis::class);
        $slave->method('get')->with($key)->willReturn($value);

        return $slave;
    }
}
