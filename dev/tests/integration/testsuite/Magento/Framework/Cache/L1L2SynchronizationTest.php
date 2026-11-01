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
 * Verifies local/remote synchronization for Zend and Symfony L1/L2 frontends.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class L1L2SynchronizationTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'l1L2Configurations')]
    public function testL1HitAndL2Fallback(
        string $configurationName,
        array $configuration
    ): void {
        $frontendA = $this->createFrontend($configuration, $configurationName, 'IT_L1L2');
        $frontendB = $this->createFrontend(
            $this->withSeparateLocalDirectory($configuration, 'b'),
            $configurationName,
            'IT_L1L2'
        );
        $id = $this->cacheId($configurationName, 'fallback', 'integration_l1l2');

        try {
            $this->assertTrue($frontendA->save('v1', $id, ['INTEGRATION_L1L2'], 3600));
            $this->assertSame('v1', $frontendA->load($id), 'Frontend A should use its L1 value');
            $this->assertSame('v1', $frontendB->load($id), 'Frontend B should fall back to L2');
            $this->assertSame('v1', $frontendB->load($id), 'Frontend B should then use its L1 value');
        } finally {
            $frontendA->remove($id);
            $frontendB->remove($id);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'l1L2Configurations')]
    public function testRemoteUpdateConvergesAcrossBothTiers(
        string $configurationName,
        array $configuration
    ): void {
        $frontendA = $this->createFrontend($configuration, $configurationName, 'IT_L1L2');
        $frontendB = $this->createFrontend(
            $this->withSeparateLocalDirectory($configuration, 'b'),
            $configurationName,
            'IT_L1L2'
        );
        $id = $this->cacheId($configurationName, 'convergence', 'integration_l1l2');

        try {
            $this->assertTrue($frontendA->save('v1', $id, ['INTEGRATION_L1L2'], 3600));
            $this->assertSame('v1', $frontendA->load($id));
            $this->assertTrue($frontendB->save('v2', $id, ['INTEGRATION_L1L2'], 3600));
            $this->assertSame('v2', $frontendA->load($id));
            $this->assertSame('v2', $frontendB->load($id));
        } finally {
            $frontendA->remove($id);
            $frontendB->remove($id);
        }
    }

    /**
     * @param array<string, mixed> $configuration
     * @return array<string, mixed>
     */
    private function withSeparateLocalDirectory(array $configuration, string $suffix): array
    {
        $localOptions = $configuration['backend_options']['local_backend_options'] ?? [];
        if (is_array($localOptions) && is_string($localOptions['cache_dir'] ?? null)) {
            $configuration['backend_options']['local_backend_options']['cache_dir']
                = $localOptions['cache_dir'] . '_' . $suffix;
        }

        return $configuration;
    }
}
