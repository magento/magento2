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
 * Verifies that large and binary cache payloads round-trip without corruption.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CachePayloadIntegrityTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testLargePayloadRoundTrip(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName, 'IT_PAYLOAD');
        $id = $this->cacheId($configurationName, 'large');
        $payload = str_repeat('integration-compressible-payload-', 5000);

        try {
            $this->assertTrue($frontend->save($payload, $id, ['INTEGRATION_PAYLOAD'], 3600));
            $this->assertSame($payload, $frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testBinaryPayloadRoundTrip(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName, 'IT_PAYLOAD');
        $id = $this->cacheId($configurationName, 'binary');
        $payload = random_bytes(4096);

        try {
            $this->assertTrue($frontend->save($payload, $id, ['INTEGRATION_BINARY'], 3600));
            $this->assertSame($payload, $frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }
}
