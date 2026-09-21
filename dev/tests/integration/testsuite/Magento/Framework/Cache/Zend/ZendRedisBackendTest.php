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
     * Cleaning by tag must also drop the id from the tag's id index, not just the data key
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

    /**
     * Cleaning by "matching any tag" (non-Lua fallback, use_lua=0)
     *
     * Must also drop the removed id from the index of every OTHER tag it carries, not just the tags passed to clean()
     */
    public function testCleanByMatchingAnyTagNonLuaCleansAllTagsForRemovedId(): void
    {
        $backend = $this->createRedisBackend(['use_lua' => 0], 'ZEND_REDIS_ANY_TAG_NO_LUA');

        $id = $this->cacheId('zend-redis', 'any_tag_no_lua');
        $passedTag = 'AC_18197_PASSED_' . uniqid();
        $otherTag = 'AC_18197_OTHER_' . uniqid();

        $backend->save('payload', $id, [$passedTag, $otherTag]);
        $this->assertContains($id, $backend->getIdsMatchingTags([$passedTag]));
        $this->assertContains($id, $backend->getIdsMatchingTags([$otherTag]));

        $backend->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [$passedTag]);

        $this->assertFalse($backend->load($id));
        $this->assertNotContains(
            $id,
            $backend->getIdsMatchingTags([$passedTag]),
            'Removed id must not remain in the passed tag id index.'
        );
        $this->assertNotContains(
            $id,
            $backend->getIdsMatchingTags([$otherTag]),
            'Removed id must not remain in the id index of a tag not passed to clean().'
        );
    }

    /**
     * With Lua enabled, cleaning by "matching any tag"
     *
     * Correctly remove the data key and the passed tag's own index.
     */
    public function testCleanByMatchingAnyTagLuaDelegatesToUpstreamImplementation(): void
    {
        $backend = $this->createRedisBackend(['use_lua' => 1], 'ZEND_REDIS_ANY_TAG_LUA');

        $id = $this->cacheId('zend-redis', 'any_tag_lua');
        $passedTag = 'AC_18197_PASSED_LUA_' . uniqid();

        $backend->save('payload', $id, [$passedTag]);
        $this->assertContains($id, $backend->getIdsMatchingTags([$passedTag]));

        $backend->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [$passedTag]);

        $this->assertFalse($backend->load($id));
        $this->assertNotContains($id, $backend->getIdsMatchingTags([$passedTag]));
    }

    /**
     * Build a Redis-backed frontend with additional backend options merged in (e.g. use_lua).
     *
     * @param array $extraOptions
     * @param string $prefix
     * @return Redis
     */
    private function createRedisBackend(array $extraOptions, string $prefix): Redis
    {
        $configuration = CacheConfigurationProvider::provide()['zend-redis'][1];
        $configuration['backend_options'] = $extraOptions + $configuration['backend_options'];

        $frontend = $this->createFrontend($configuration, 'zend-redis', $prefix);
        $backend = $frontend->getBackend();
        $this->assertInstanceOf(Redis::class, $backend);

        return $backend;
    }
}
