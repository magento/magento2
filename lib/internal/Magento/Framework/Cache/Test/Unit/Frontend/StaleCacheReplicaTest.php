<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Test\Unit\Frontend;

use Magento\Framework\Cache\Frontend\Adapter\InMemoryCache;
use Magento\Framework\Cache\Frontend\Adapter\Zend;
use Magento\Framework\Cache\Frontend\StaleCacheReplica;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Lock\Backend\InMemoryLock;
use Magento\Framework\Lock\LockManagerInterface;
use PHPUnit\Framework\TestCase;
use Zend_Cache;

/**
 * Test cases for stale cache replica
 */
class StaleCacheReplicaTest extends TestCase
{
    /** @var InMemoryCache */
    private $masterCache;

    /** @var InMemoryCache */
    private $slaveCache;

    /** @var StaleCacheReplica */
    private $cache;

    /** @var LockManagerInterface */
    private $lockManager;

    protected function setUp()
    {
        $this->masterCache = new InMemoryCache();
        $this->slaveCache = new InMemoryCache();
        $this->lockManager = new InMemoryLock();

        $this->cache = $this->createCache();
    }

    /** @test */
    public function savesCacheIntoMaster()
    {
        $this->cache->save('data_one', 'cache_one');

        $this->assertEquals(
            'data_one',
            $this->masterCache->load('cache_one')
        );
    }

    /** @test */
    public function savesCacheIntoSlave()
    {
        $this->cache->save('data_two', 'cache_two');

        $this->assertEquals(
            'data_two',
            $this->slaveCache->load('cache_two')
        );
    }

    /** @test */
    public function invalidatesCacheByLifeTimeInMaster()
    {
        $this->cache->save('data_two', 'cache_two', [], 0.005);

        usleep(6000);
        $this->assertEquals(
            false,
            $this->masterCache->load('cache_two')
        );
    }
    
    /** @test */
    public function ignoresLifetimeOfCacheForSlave()
    {
        $this->cache->save('data_one', 'cache_one', [], 0.005);
        usleep(6000);
        $this->assertEquals(
            'data_one',
            $this->slaveCache->load('cache_one')
        );
    }

    /** @test */
    public function cleansCacheByTagsInMaster()
    {
        $this->cache->save('data_one', 'cache_one', ['tag_one']);

        $this->cache->clean($this->masterCache::CLEAN_MATCHING_TAG, ['tag_one']);

        $this->assertEquals(false, $this->masterCache->load('cache_one'));
    }

    /** @test */
    public function preservesCleanedCacheByTagsInSlave()
    {
        $this->cache->save('data_one', 'cache_one', ['tag_one']);

        $this->slaveCache->clean($this->slaveCache::CLEAN_MATCHING_TAG, ['tag_one']);

        $this->assertEquals('data_one', $this->slaveCache->load('cache_one'));
    }

    /** @test */
    public function loadsDataFromMasterWhenNotLoaded()
    {
        $this->masterCache->save('data_in_master', 'cache_one');
        $this->slaveCache->save('data_in_slave', 'cache_one');

        $this->assertEquals('data_in_master', $this->cache->load('cache_one'));
    }

    /** @test */
    public function loadsDataFromSlaveWhenCacheIsLocked()
    {
        $this->slaveCache->save('data_in_slave', 'cache_one');

        $this->lockManager->lock('lock_name');

        $this->assertEquals('data_in_slave', $this->cache->load('cache_one'));
    }

    /** @test */
    public function loadsDataFromMasterWhenCacheIsLockedIfItIsAvailable()
    {
        $this->masterCache->save('data_in_master', 'cache_one');
        $this->slaveCache->save('data_in_slave', 'cache_one');

        $this->lockManager->lock('lock_name');

        $this->assertEquals('data_in_master', $this->cache->load('cache_one'));
    }

    /** @test */
    public function ignoresDataInSlaveIfCacheIsNotLocked()
    {
        $this->slaveCache->save('data_in_slave', 'cache_one');

        $this->assertEquals(false, $this->cache->load('cache_one'));
    }

    /** @test */
    public function removesCacheByIdentifierFromMaster()
    {
        $this->masterCache->save('some_data', 'cache_one');

        $this->cache->remove('cache_one');

        $this->assertFalse($this->masterCache->test('cache_one'));
    }
    
    /** @test */
    public function keepsCacheInSlaveWhenCacheIsRemovedByIdentifier()
    {
        $this->slaveCache->save('some_data', 'cache_one');

        $this->cache->remove('cache_one');

        $this->assertTrue($this->slaveCache->test('cache_one'));
    }

    /** @test */
    public function ignoresIdentifiersInSlaveFromMasterOnlyList()
    {
        $this->slaveCache->save('data_in_slave', 'master_only_identifier');

        $this->lockManager->lock('lock_name');

        $this->assertEquals(false, $this->cache->load('master_only_identifier'));
    }


    /** @test */
    public function returnsBackendFromMasterCache()
    {
        $zendCache = Zend_Cache::factory('core', 'test');
        $replica = $this->createCache(
            new Zend(function () use ($zendCache) {
                return $zendCache;
            })
        );

        $this->assertEquals($zendCache->getBackend(), $replica->getBackend());
    }

    /** @test */
    public function returnsLowLevelFrontendFromMasterCache()
    {
        $zendCache = Zend_Cache::factory('core', 'test');
        $replica = $this->createCache(
            new Zend(function () use ($zendCache) {
                return $zendCache;
            })
        );

        $this->assertEquals($zendCache, $replica->getLowLevelFrontend());
    }

    private function createCache(FrontendInterface $customMaster = null)
    {
        return new StaleCacheReplica(
            $customMaster ?? $this->masterCache,
            $this->slaveCache,
            $this->lockManager,
            'lock_name',
            ['master_only_identifier']
        );
    }
}
