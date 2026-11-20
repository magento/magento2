<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\TestFramework\Cache\CacheFrontendTestCase;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;

/**
 * Verifies cache lifetime behavior across all configured cache implementations.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class TtlTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testFiniteLifetime(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName);
        $id = $this->cacheId($configurationName, 'finite', 'integration_ttl');

        try {
            $this->assertTrue($frontend->save('finite-value', $id, [], 3600));
            $this->assertSame('finite-value', $frontend->load($id));
            $expiration = $frontend->test($id);
            $this->assertIsInt($expiration);
            $this->assertGreaterThanOrEqual(time(), $expiration);
        } finally {
            $frontend->remove($id);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testNullLifetimeDoesNotExpire(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName);
        $id = $this->cacheId($configurationName, 'persistent', 'integration_ttl');

        try {
            $this->assertTrue($frontend->save('persistent-value', $id, [], null));
            $this->assertSame('persistent-value', $frontend->load($id));
            $this->assertNotFalse($frontend->test($id));
        } finally {
            $frontend->remove($id);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testExpiredEntryIsNotLoaded(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName);
        $id = $this->cacheId($configurationName, 'expired', 'integration_ttl');

        try {
            $this->assertTrue($frontend->save('expired-value', $id, [], 1));
            $this->assertSame('expired-value', $frontend->load($id));
            sleep(2);
            $this->assertFalse($frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testCleanOldRemovesExpiredEntries(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName);
        $expiredId = $this->cacheId($configurationName, 'old', 'integration_ttl');
        $freshId = $this->cacheId($configurationName, 'fresh', 'integration_ttl');

        try {
            $frontend->save('expired-value', $expiredId, [], 1);
            $frontend->save('fresh-value', $freshId, [], 3600);
            sleep(2);

            try {
                $this->assertTrue($frontend->clean(CacheConstants::CLEANING_MODE_OLD));
            } catch (\InvalidArgumentException $exception) {
                $this->markTestSkipped(
                    $configurationName . ' frontend does not expose CLEANING_MODE_OLD'
                );
            }

            $this->assertFalse($frontend->load($expiredId));
            $this->assertSame('fresh-value', $frontend->load($freshId));
        } finally {
            $frontend->remove($expiredId);
            $frontend->remove($freshId);
        }
    }
}
