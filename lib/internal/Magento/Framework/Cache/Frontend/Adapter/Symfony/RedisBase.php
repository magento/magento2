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
        // phpredis absent: alias to the stub declared in its own file, under its own distinct class
        // name (RedisBaseStub). This keeps this file to side effects only and, just as importantly,
        // means no file anywhere statically declares a class literally named RedisBase — so an
        // optimized/authoritative Composer classmap can never resolve RedisBase to anything other
        // than this conditional (a literal "class RedisBase" in a second file would let the classmap
        // generator bind that name to the stub file, permanently shadowing the phpredis branch).
        require_once __DIR__ . '/RedisBaseStub.php';
        class_alias(RedisBaseStub::class, RedisBase::class);
    }
}
