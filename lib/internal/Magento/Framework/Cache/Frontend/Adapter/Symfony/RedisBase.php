<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

/*
 * Resolves the base class that SlaveAwareRedis extends, so that class is loadable and compilable on
 * EVERY host — including one without the phpredis extension.
 *
 * The read-replica connection must be a real \Redis when phpredis is present: Symfony's RedisAdapter
 * requires a \Redis instance and instantiates the subclass by name (new $class()), so SlaveAwareRedis
 * has to BE a \Redis, not merely wrap one. But `class SlaveAwareRedis extends \Redis` cannot even be
 * declared when the extension is absent — the parent does not exist — and setup:di:compile
 * require_once's/reflects every class file, which would fatal with "Class \"Redis\" not found".
 *
 * So the parent is indirected through this module-owned RedisBase:
 *   - phpredis present -> RedisBase is an alias of \Redis  -> SlaveAwareRedis IS a \Redis subclass
 *                                                             (Symfony accepts it; instanceof \Redis
 *                                                             and is_a(..., \Redis::class) hold).
 *   - phpredis absent  -> RedisBase is a minimal stub      -> SlaveAwareRedis still loads/compiles;
 *                                                             it is never instantiated on that host
 *                                                             (the provider uses Predis instead).
 * This keeps the whole concern inside the cache module — no di:compile scanner or command changes.
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
