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
 * Verifies that different cache frontend prefixes do not collide.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class FrontendIsolationTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testDifferentIdPrefixesAreIsolated(
        string $configurationName,
        array $configuration
    ): void {
        $frontendA = $this->createFrontend($configuration, $configurationName . '-a', 'IT_ISOLATION');
        $frontendB = $this->createFrontend($configuration, $configurationName . '-b', 'IT_ISOLATION');
        $id = 'integration_isolation_' . str_replace('-', '_', $configurationName) . '_' . uniqid();

        try {
            $frontendA->remove($id);
            $frontendB->remove($id);

            $this->assertTrue($frontendA->save('from-a', $id, [], 3600));
            $this->assertFalse($frontendB->load($id));

            $this->assertTrue($frontendB->save('from-b', $id, [], 3600));
            $this->assertSame('from-a', $frontendA->load($id));
            $this->assertSame('from-b', $frontendB->load($id));
        } finally {
            $frontendA->remove($id);
            $frontendB->remove($id);
        }
    }
}
