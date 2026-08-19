<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

/*
 * Resolves a Redis-compatible base class so SlaveAwareRedis can load on every host.
 * Uses \Redis when phpredis is available, preserving Symfony compatibility.
 * Falls back to a minimal stub when absent, allowing setup:di:compile to succeed.
 */
if (!class_exists(RedisBase::class, false)) {
    if (class_exists(\Redis::class)) {
        // phpredis present: base IS \Redis, so SlaveAwareRedis inherits the real client.
        class_alias(\Redis::class, RedisBase::class);
    } else {
        /**
         * Stub base used only when phpredis is absent. SlaveAwareRedis is never instantiated here, so
         * this only has to make the subclass declarable and reflectable for di:compile.
         */
        class RedisBase
        {
        }
    }
}
