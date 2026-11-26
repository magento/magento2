<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;
use PHPUnit\Framework\Attributes\DataProviderExternal;

class LuaTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'redisConfigurations')]
    public function testTagCleanupWithLuaEnabled(
        string $configurationName,
        array $configuration
    ): void {
        $configuration['backend_options']['use_lua'] = true;
        $configuration['backend_options']['use_lua_on_gc'] = true;
        $frontend = $this->createFrontend($configuration, $configurationName, 'COMMON_LUA');
        $matching = $this->cacheId($configurationName, 'lua_matching');
        $other = $this->cacheId($configurationName, 'lua_other');

        try {
            $this->assertTrue($frontend->save('matching', $matching, ['COMMON_LUA_TAG'], 3600));
            $this->assertTrue($frontend->save('other', $other, ['COMMON_LUA_OTHER'], 3600));
            $this->assertTrue($frontend->clean(CacheConstants::CLEANING_MODE_MATCHING_TAG, ['COMMON_LUA_TAG']));
            $this->assertFalse($frontend->load($matching));
            $this->assertSame('other', $frontend->load($other));
        } finally {
            $frontend->remove($matching);
            $frontend->remove($other);
        }
    }
}
