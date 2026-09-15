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
