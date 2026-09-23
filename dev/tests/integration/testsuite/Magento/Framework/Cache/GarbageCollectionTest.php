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

/**
 * Verifies backend-level expired-entry garbage collection for Redis implementations.
 */
class GarbageCollectionTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'redisConfigurations')]
    public function testCleanOldPrunesExpiredEntriesAndKeepsFreshEntries(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName, 'COMMON_GC');
        $expired = $this->cacheId($configurationName, 'gc_expired');
        $fresh = $this->cacheId($configurationName, 'gc_fresh');

        try {
            $this->assertTrue($frontend->save('expired', $expired, ['COMMON_GC'], 1));
            $this->assertTrue($frontend->save('fresh', $fresh, ['COMMON_GC'], 3600));
            sleep(2);
            $this->assertTrue($frontend->getBackend()->clean(CacheConstants::CLEANING_MODE_OLD));
            $this->assertFalse($frontend->load($expired));
            $this->assertSame('fresh', $frontend->load($fresh));
        } finally {
            $frontend->remove($expired);
            $frontend->remove($fresh);
        }
    }
}
