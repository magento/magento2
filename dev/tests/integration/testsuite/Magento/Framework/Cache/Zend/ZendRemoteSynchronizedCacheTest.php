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
 * Verifies legacy RemoteSynchronizedCache backend behavior.
 */
class ZendRemoteSynchronizedCacheTest extends CacheFrontendTestCase
{
    public function testRemoteSynchronizedBackendStoresAndLoadsData(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-l1-l2'][1];
        $frontend = $this->createFrontend($configuration, 'zend-l1-l2', 'ZEND_REMOTE_SYNC');
        $id = $this->cacheId('zend-l1-l2', 'remote_metadata');

        try {
            $this->assertInstanceOf(\Zend_Cache_Backend_ExtendedInterface::class, $frontend->getBackend());
            $this->assertTrue($frontend->save('remote-value', $id, ['ZEND_REMOTE_SYNC'], 3600));
            $this->assertSame('remote-value', $frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }
}
