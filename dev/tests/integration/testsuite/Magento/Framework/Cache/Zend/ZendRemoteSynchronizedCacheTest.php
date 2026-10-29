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

    /**
     * With a Redis remote, save()/remove() must write/remove the data key and its paired
     * ':hash' version-marker key together, never one without the other (AC-18194).
     */
    public function testSaveAndRemoveKeepDataAndVersionKeysInSync(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-l1-l2'][1];
        $frontend = $this->createFrontend($configuration, 'zend-l1-l2', 'ZEND_ATOMIC_PAIR');
        $id = $this->cacheId('zend-l1-l2', 'atomic_pair');

        $redisOptions = $configuration['backend_options']['remote_backend_options'];
        $raw = new \Credis_Client(
            $redisOptions['server'],
            $redisOptions['port'],
            null,
            '',
            $redisOptions['database'],
            $redisOptions['password'] ?: null
        );

        // The Zend frontend adapter uppercases every id (Adapter\Zend::_unifyId()) before it
        // reaches the backend, so the raw Redis key uses strtoupper($id), not $id as-is.
        $backendId = strtoupper($id);

        try {
            $this->assertTrue($frontend->save('atomic-value', $id, ['ZEND_ATOMIC_PAIR'], 3600));

            $this->assertNotEmpty(
                (array) $raw->keys('zc:k:*' . $backendId),
                'data key must exist after save()'
            );
            $this->assertNotEmpty(
                (array) $raw->keys('zc:k:*' . $backendId . ':hash'),
                'paired hash key must exist after save()'
            );

            $this->assertTrue($frontend->remove($id));

            $this->assertEmpty(
                (array) $raw->keys('zc:k:*' . $backendId),
                'data key must be gone after remove()'
            );
            $this->assertEmpty(
                (array) $raw->keys('zc:k:*' . $backendId . ':hash'),
                'paired hash key must be gone after remove()'
            );
        } finally {
            $frontend->remove($id);
        }
    }
}
