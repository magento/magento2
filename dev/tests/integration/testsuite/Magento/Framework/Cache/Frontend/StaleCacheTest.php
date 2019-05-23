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
