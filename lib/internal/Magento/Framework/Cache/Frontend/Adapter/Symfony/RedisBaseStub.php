<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

/**
 * Stub base used only when phpredis is absent. SlaveAwareRedis is never instantiated on such a
 * host (the provider uses Predis instead) — this only has to make the subclass declarable and
 * reflectable for di:compile.
 *
 * Deliberately named RedisBaseStub, not RedisBase: RedisBase.php aliases this class to the
 * RedisBase name at runtime via class_alias(), so no file statically declares a class literally
 * named RedisBase (see RedisBase.php for why that distinction matters for Composer's classmap).
 */
class RedisBaseStub
{
}
