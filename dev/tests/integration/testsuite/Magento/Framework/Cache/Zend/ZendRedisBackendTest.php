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
     * Cleaning by tag must also drop the id from the tag's id index, not just the data key (AC-18197).
     */
    public function testCleanByMatchingTagRemovesIdFromTagIndex(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-redis'][1];
        $frontend = $this->createFrontend($configuration, 'zend-redis', 'ZEND_REDIS');
        $backend = $frontend->getBackend();
        $this->assertInstanceOf(Redis::class, $backend);

        $id = $this->cacheId('zend-redis', 'tag_index');
        $tag = 'AC_18197_TAG_' . uniqid();

        $backend->save('payload', $id, [$tag]);
        $this->assertContains($id, $backend->getIdsMatchingTags([$tag]));

        $backend->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [$tag]);

        $this->assertFalse($backend->load($id));
        $this->assertNotContains(
            $id,
            $backend->getIdsMatchingTags([$tag]),
            'Removed id must not remain in the tag id index.'
        );
    }
}
