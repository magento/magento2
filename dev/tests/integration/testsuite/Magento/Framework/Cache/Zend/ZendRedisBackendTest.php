<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Zend;

use Magento\Framework\Cache\Backend\Redis;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Verifies legacy Magento Redis backend-specific APIs.
 */
class ZendRedisBackendTest extends CacheFrontendTestCase
{
    public function testRedisBackendReportsFillingPercentage(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-redis'][1];
        $frontend = $this->createFrontend($configuration, 'zend-redis', 'ZEND_REDIS');
        $backend = $frontend->getBackend();

        $this->assertInstanceOf(Redis::class, $backend);
        $this->assertIsNumeric($backend->getFillingPercentage());
        $this->assertGreaterThanOrEqual(0, (float) $backend->getFillingPercentage());
    }

    /**
     * saveAtomicPair()/removeAtomicPair() must write/remove both paired entries together,
     * including their tag index entries, in a single transaction (AC-18194).
     */
    public function testAtomicPairKeepsBothEntriesInSync(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-redis'][1];
        $frontend = $this->createFrontend($configuration, 'zend-redis', 'ZEND_REDIS');
        $backend = $frontend->getBackend();
        $this->assertInstanceOf(Redis::class, $backend);

        $id = $this->cacheId('zend-redis', 'atomic_pair');
        $pairedId = $id . ':hash';
        $tag = 'AC_18194_TAG_' . uniqid();

        try {
            $saved = $backend->saveAtomicPair(
                'atomic-data',
                $id,
                [$tag],
                3600,
                'atomic-hash',
                $pairedId,
                [$tag],
                3600
            );
            $this->assertTrue($saved);
            $this->assertSame('atomic-data', $backend->load($id));
            $this->assertSame('atomic-hash', $backend->load($pairedId));
            $this->assertContains($id, $backend->getIdsMatchingTags([$tag]));
            $this->assertContains($pairedId, $backend->getIdsMatchingTags([$tag]));

            $removed = $backend->removeAtomicPair($id, $pairedId);
            $this->assertTrue($removed);
            $this->assertFalse($backend->load($id));
            $this->assertFalse($backend->load($pairedId));
            $this->assertNotContains($id, $backend->getIdsMatchingTags([$tag]));
            $this->assertNotContains($pairedId, $backend->getIdsMatchingTags([$tag]));
        } finally {
            $backend->remove($id);
            $backend->remove($pairedId);
        }
    }
}
