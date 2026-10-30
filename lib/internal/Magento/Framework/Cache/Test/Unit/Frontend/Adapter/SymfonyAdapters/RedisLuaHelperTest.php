<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\SymfonyAdapters;

use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisLuaHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RedisLuaHelper.
 *
 * The Lua-execution paths need a Redis client whose EVAL family is stubbable, which requires the
 * phpredis extension (Predis dispatches commands through __call and cannot be stubbed); those tests
 * are skipped when phpredis is absent. The constructor-validation test always runs.
 */
class RedisLuaHelperTest extends TestCase
{
    /**
     * The typed constructor rejects anything that is neither a phpredis nor a Predis client
     * at the language level (union type), so an unsupported object raises a TypeError.
     */
    public function testConstructorRejectsInvalidConnection(): void
    {
        $this->expectException(\TypeError::class);

        new RedisLuaHelper(new \stdClass());
    }

    /**
     * When Lua is disabled by flag, isEnabled() short-circuits to false without touching Redis.
     */
    public function testIsEnabledReturnsFalseWhenDisabled(): void
    {
        $helper = new RedisLuaHelper($this->createRedisMock(), false);

        $this->assertFalse($helper->isEnabled());
    }

    /**
     * isEnabled() returns true when a trivial EVAL succeeds.
     */
    public function testIsEnabledReturnsTrueWhenEvalSucceeds(): void
    {
        $redis = $this->createRedisMock();
        $redis->method('eval')->willReturn(1);

        $helper = new RedisLuaHelper($redis, true);

        $this->assertTrue($helper->isEnabled());
    }

    /**
     * isEnabled() swallows EVAL failures and reports false (server without Lua support).
     */
    public function testIsEnabledReturnsFalseWhenEvalFails(): void
    {
        $redis = $this->createRedisMock();
        $redis->method('eval')->willThrowException(new \RuntimeException('NOSCRIPT'));

        $helper = new RedisLuaHelper($redis, true);

        $this->assertFalse($helper->isEnabled());
    }

    /**
     * All operations return neutral values when Lua is disabled, never calling Redis.
     */
    public function testOperationsReturnNeutralValuesWhenDisabled(): void
    {
        $helper = new RedisLuaHelper($this->createRedisMock(), false);

        $this->assertSame(0, $helper->cleanByTagConditional('cache:tags:x', 'px_'));
        $this->assertFalse($helper->atomicSaveWithTags('key', 'value', 60, ['t1'], 'rev'));
        $this->assertSame([0, 0], $helper->garbageCollect('px_*', 'cache:tags:px_'));
        $this->assertSame(0, $helper->clearAllIndices('px_'));
    }

    /**
     * clearScriptCache() must not raise.
     */
    public function testClearScriptCacheDoesNotThrow(): void
    {
        $helper = new RedisLuaHelper($this->createRedisMock(), true);

        $this->assertNull($helper->clearScriptCache());
    }

    /**
     * @return \Redis|MockObject
     */
    private function createRedisMock()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('phpredis extension is not loaded.');
        }

        return $this->createMock(\Redis::class);
    }
}
