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
 * Verifies Redis connection usability for both Magento cache implementations.
 */
class RedisConnectionTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'redisConfigurations')]
    public function testSeparateFrontendsShareRedisData(
        string $configurationName,
        array $configuration
    ): void {
        $writer = $this->createFrontend($configuration, $configurationName, 'COMMON_CONNECTION');
        $reader = $this->createFrontend($configuration, $configurationName, 'COMMON_CONNECTION');
        $id = $this->cacheId($configurationName, 'connection');

        try {
            $this->assertTrue($writer->save('connection-value', $id, [], 3600));
            $this->assertSame('connection-value', $reader->load($id));
        } finally {
            $writer->remove($id);
            $reader->remove($id);
        }
    }
}
