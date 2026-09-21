<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Zend;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Verifies that the legacy Redis backend receives Magento's configured options.
 */
class ZendBackendCompatibilityTest extends CacheFrontendTestCase
{
    public function testLegacyRedisBackendIsInitializedWithConfiguredOptions(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-redis'][1];
        $frontend = $this->createFrontend($configuration, 'zend-redis', 'ZEND_COMPAT');
        $backend = $frontend->getBackend();

        $this->assertInstanceOf(\Magento\Framework\Cache\Backend\Redis::class, $backend);
        $id = $this->cacheId('zend-redis', 'backend_compatibility');
        try {
            $this->assertTrue($frontend->save('legacy-value', $id));
            $this->assertSame('legacy-value', $frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }
}
