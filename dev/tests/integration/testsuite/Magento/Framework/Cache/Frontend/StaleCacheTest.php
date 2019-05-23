<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Frontend;

use Magento\Framework\App\Cache\Type\Config;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoCache config enabled
 */
class StaleCacheTest extends TestCase
{
    /** @var StaleCache */
    private $staleCache;

    /** @var Config */
    private $cache;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->staleCache = $objectManager->create(
            StaleCache::class,
            [
                'identifierFormat' => 'stale_%s_stale',
                'cacheType' => Config::TYPE_IDENTIFIER,
                'cacheTag' => 'CONFIG_STALE'
            ]
        );
        $this->cache = $objectManager->create(Config::class);
    }

    /** @test */
    public function savesCacheEntryWithFormattedIdentifiers()
    {
        $this->staleCache->save('stale_cache_entry', 'cache_key');

        $this->assertEquals(
            'stale_cache_entry',
            $this->cache->load('stale_cache_key_stale')
        );
    }


    /** @test */
    public function loadsCacheByUsingFormattedIdentifier()
    {
        $this->cache->save('some_data_in_cache', 'stale_some_key_stale');

        $this->assertEquals(
            'some_data_in_cache',
            $this->staleCache->load('some_key')
        );
    }

    /** @test */
    public function removesCacheByUsingFormattedIdentifier()
    {
        $this->cache->save('some_data_in_cache', 'stale_some_key_stale');
        $this->staleCache->remove('some_key');

        $this->assertEquals(
            false,
            $this->cache->load('stale_some_key_stale')
        );
    }

    /** @test */
    public function cleansOnlyStaleCacheWhenAllModeSpecified()
    {
        $this->staleCache->save('data_in_stale_cache', 'some_data');
        $this->cache->save('data_in_regular_cache', 'some_regular_data');

        $this->staleCache->clean();

        $this->assertEquals(
            [
                false,
                'data_in_regular_cache'
            ],
            [
                $this->cache->load('stale_some_data_stale'),
                $this->cache->load('some_regular_data')
            ]
        );
    }

    /** @test */
    public function cleansCacheForAllCacheEntriesMatchingTagCombination()
    {
        $this->staleCache->save('data_in_stale_cache_tag_one', 'some_data_tag_one', ['tag_one']);
        $this->staleCache->save('data_in_stale_cache_tag_two', 'some_data_tag_two', ['tag_two']);
        $this->cache->save('data_in_regular_cache', 'some_regular_data', ['tag_one']);

        $this->staleCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['tag_one']);

        $this->assertEquals(
            [
                false,
                'data_in_stale_cache_tag_two',
                'data_in_regular_cache'
            ],
            [
                $this->cache->load('stale_some_data_tag_one_stale'),
                $this->cache->load('stale_some_data_tag_two_stale'),
                $this->cache->load('some_regular_data')
            ]
        );
    }

    /** @test */
    public function cleansCacheForBothTagsMatchingTheEntry()
    {
        $this->staleCache->save('data_in_stale_cache_tag_one', 'some_data_tag_one', ['tag_one', 'tag_two']);
        $this->staleCache->save('data_in_stale_cache_tag_two', 'some_data_tag_two', ['tag_two']);
        $this->cache->save('data_in_regular_cache', 'some_regular_data', ['tag_one']);

        $this->staleCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['tag_one', 'tag_two']);

        $this->assertEquals(
            [
                false,
                'data_in_stale_cache_tag_two',
                'data_in_regular_cache'
            ],
            [
                $this->cache->load('stale_some_data_tag_one_stale'),
                $this->cache->load('stale_some_data_tag_two_stale'),
                $this->cache->load('some_regular_data')
            ]
        );
    }

    /** @test */
    public function cleansCacheForAnyTagMatch()
    {
        $this->staleCache->save('data_in_stale_cache_tag_one', 'some_data_tag_one', ['tag_one', 'tag_two']);
        $this->staleCache->save('data_in_stale_cache_tag_two', 'some_data_tag_two', ['tag_two']);
        $this->cache->save('data_in_regular_cache', 'some_regular_data', ['tag_one']);

        $this->staleCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['tag_one', 'tag_two']);

        $this->assertEquals(
            [
                false,
                false,
                'data_in_regular_cache'
            ],
            [
                $this->cache->load('stale_some_data_tag_one_stale'),
                $this->cache->load('stale_some_data_tag_two_stale'),
                $this->cache->load('some_regular_data')
            ]
        );
    }

    /** @test */
    public function usesIdentifierFormatToCheckForCacheAvailability()
    {
        $this->staleCache->save('some_cached_data', 'some_data_in_cache');

        $this->assertNotEquals(false, $this->staleCache->test('some_data_in_cache'));
    }

    /** @test */
    public function returnsFalseIfDataIsNotInStaleCache()
    {
        $this->assertFalse($this->staleCache->test('some_data_not_in_cache'));
    }
    
    /** @test */
    public function returnsSameBackendAsConfigCache()
    {
        $this->assertSame(
            $this->cache->getBackend(),
            $this->staleCache->getBackend()
        );
    }

    /** @test */
    public function returnsSameFrontendAsConfigCache()
    {
        $this->assertSame(
            $this->cache->getLowLevelFrontend(),
            $this->staleCache->getLowLevelFrontend()
        );
    }
}
