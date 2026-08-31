<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

// phpcs:ignoreFile PSR1.Files.SideEffects -- must combine the conditional declaration and the
// class_alias() side effect in this one file: Composer's classmap generator is a static token scan,
// so an authoritative/optimized classmap (composer dump-autoload -o -a) can only resolve "RedisBase"
// correctly if it maps back to this exact file's own runtime branch (see comment below). Splitting
// the phpredis-absent declaration into a second file would leave no literal "class RedisBase" token
// anywhere for the classmap to find at all, breaking autoloading under classmap-authoritative on
// every host; declaring it in a second file under the SAME name would let the classmap bind
// "RedisBase" to that file unconditionally, bypassing this file's phpredis-present branch even on
// hosts that DO have phpredis. Neither alternative is safe, so this file is deliberately exempted.
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
        // phpredis absent: declare a real, empty class literally named RedisBase, in this same file,
        // so Composer's classmap generator (a static token scan, not code execution) always resolves
        // "RedisBase" back to this file regardless of which branch runs on a given host. Aliasing to a
        // class declared in a second file would leave no file anywhere with a literal "class RedisBase"
        // token, which an optimized/authoritative classmap (composer dump-autoload -o -a) cannot
        // resolve at all — it fatals with "Class RedisBase not found" on every host, phpredis-present
        // or absent, the moment SlaveAwareRedis needs its parent.
        class RedisBase
        {
        }
    }
}
