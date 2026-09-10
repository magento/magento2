<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\Frontend\Adapter\Symfony\RedisBase;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the RedisBase compatibility shim.
 *
 * RedisBase resolves to \Redis when phpredis is available and to a minimal stub otherwise, so that
 * SlaveAwareRedis is always declarable (including during setup:di:compile on hosts without phpredis).
 */
class RedisBaseTest extends TestCase
{
    /**
     * The class must always be resolvable regardless of whether phpredis is installed.
     */
    public function testRedisBaseIsAlwaysAvailable(): void
    {
        $this->assertTrue(class_exists(RedisBase::class));
    }

    /**
     * When phpredis is present, RedisBase must alias the real \Redis client so SlaveAwareRedis
     * inherits its behavior.
     */
    public function testRedisBaseAliasesPhpRedisWhenAvailable(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('phpredis extension is not loaded.');
        }

        $this->assertTrue(
            is_a(RedisBase::class, \Redis::class, true),
            'RedisBase must be an alias of \Redis when phpredis is available'
        );
    }
}
