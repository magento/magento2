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
 * Verifies common L1-first and L2-fallback behavior for Zend and Symfony caches.
 */
class L1L2ReadBehaviorTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'l1L2Configurations')]
    public function testL1HitAndL2Fallback(string $configurationName, array $configuration): void
    {
        $frontendA = $this->createFrontend($configuration, $configurationName, 'IT_READ_BEHAVIOR');
        $frontendB = $this->createFrontend(
            $this->withSeparateLocalDirectory($configuration),
            $configurationName,
            'IT_READ_BEHAVIOR'
        );
        $id = $this->cacheId($configurationName, 'read_behavior', 'integration');

        try {
            $this->assertTrue($frontendA->save('l1-value', $id, ['INTEGRATION_READ_BEHAVIOR'], 3600));
            $this->assertSame('l1-value', $frontendA->load($id));
            $this->assertSame('l1-value', $frontendB->load($id));
        } finally {
            $frontendA->remove($id);
            $frontendB->remove($id);
        }
    }

    private function withSeparateLocalDirectory(array $configuration): array
    {
        $localOptions = $configuration['backend_options']['local_backend_options'] ?? [];
        if (is_string($localOptions['cache_dir'] ?? null)) {
            $configuration['backend_options']['local_backend_options']['cache_dir'] .= '_read_behavior_b';
        }

        return $configuration;
    }
}
