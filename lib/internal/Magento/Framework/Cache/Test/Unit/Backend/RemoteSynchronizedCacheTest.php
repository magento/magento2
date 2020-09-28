<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Backend;

use Magento\Framework\Cache\Backend\Database;
use Magento\Framework\Cache\Backend\RemoteSynchronizedCache;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoteSynchronizedCacheTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Cm_Cache_Backend_File|MockObject
     */
    private $localCacheMockExample;

    /**
     * @var Database|MockObject
     */
    private $remoteCacheMockExample;

    /**
     * @var RemoteSynchronizedCache
     */
    private $remoteSyncCacheInstance;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->localCacheMockExample = $this->getMockBuilder(\Cm_Cache_Backend_File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->remoteCacheMockExample = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Framework\Cache\Backend\Database $databaseCacheInstance */

        $this->remoteSyncCacheInstance = $this->objectManager->getObject(
            RemoteSynchronizedCache::class,
            [
                'options' => [
                    'remote_backend' => $this->remoteCacheMockExample,
                    'local_backend' => $this->localCacheMockExample,
                ],
            ]
        );
    }

    /**
     * Test that exception is thrown if cache is not configured.
     *
     * @param array $options
     *
     * @dataProvider initializeWithExceptionDataProvider
     */
    public function testInitializeWithException($options): void
    {
        $this->expectException('Zend_Cache_Exception');
        $this->objectManager->getObject(
            RemoteSynchronizedCache::class,
            [
                'options' => $options,
            ]
        );
    }

    /**
     * @return array
     */
    public function initializeWithExceptionDataProvider(): array
    {
        return [
            'empty_backend_option' => [
                'options' => [
                    'remote_backend' => null,
                    'local_backend' => null,
                ],
            ],
            'empty_remote_backend_option' => [
                'options' => [
                    'remote_backend' => Database::class,
                    'local_backend' => null,
                ],
            ],
            'empty_local_backend_option' => [
                'options' => [
                    'remote_backend' => null,
                    'local_backend' => \Cm_Cache_Backend_File::class,
                ],
            ],
        ];
    }

    /**
     * Test that exception is not thrown if cache is configured.
     *
     * @param array $options
     *
     * @dataProvider initializeWithOutExceptionDataProvider
     */
    public function testInitializeWithOutException($options): void
    {
        $result = $this->objectManager->getObject(
            RemoteSynchronizedCache::class,
            [
                'options' => $options,
            ]
        );
        $this->assertInstanceOf(RemoteSynchronizedCache::class, $result);
    }

    /**
     * @return array
     */
    public function initializeWithOutExceptionDataProvider(): array
    {
        $connectionMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'not_empty_backend_option' => [
                'options' => [
                    'remote_backend' => Database::class,
                    'remote_backend_options' => [
                        'adapter_callback' => '',
                        'data_table' => 'data_table',
                        'data_table_callback' => 'data_table_callback',
                        'tags_table' => 'tags_table',
                        'tags_table_callback' => 'tags_table_callback',
                        'store_data' => '',
                        'adapter' => $connectionMock,
                    ],
                    'local_backend' => \Cm_Cache_Backend_File::class,
                    'local_backend_options' => [
                        'cache_dir' => '/tmp',
                    ],
                ],
            ],
        ];
    }

    /**
     * Test that load will return the newest data.
     *
     * @return void
     */
    public function testLoad(): void
    {
        $localData = 1;
        $remoteData = 2;

        $this->localCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn($localData);

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn(\hash('sha256', (string)$remoteData));

        $this->remoteCacheMockExample
            ->expects($this->at(1))
            ->method('load')
            ->willReturn($remoteData);

        $this->localCacheMockExample
            ->expects($this->atLeastOnce())
            ->method('save')
            ->with($remoteData)
            ->willReturn(true);

        $this->assertEquals($remoteData, $this->remoteSyncCacheInstance->load(1));
    }

    /**
     * Test that load will not return data when no local data and no remote data exist.
     *
     * @return void
     */
    public function testLoadWithNoLocalAndNoRemoteData(): void
    {
        $localData = false;
        $remoteData = false;

        $this->localCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn($localData);

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn($remoteData);

        $this->assertEquals(false, $this->remoteSyncCacheInstance->load(1));
    }

    /**
     * Test that load will return the newest data when only remote data exists.
     *
     * @return void
     */
    public function testLoadWithNoLocalAndWithRemoteData(): void
    {
        $localData = false;
        $remoteData = 1;

        $this->localCacheMockExample
            ->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($localData);

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn($remoteData);

        $this->localCacheMockExample
            ->expects($this->atLeastOnce())
            ->method('save')
            ->willReturn(true);

        $this->assertEquals($remoteData, $this->remoteSyncCacheInstance->load(1));
    }

    /**
     * Test that load will return the newest data when local data and remote data are the same.
     *
     * @return void
     */
    public function testLoadWithEqualLocalAndRemoteData(): void
    {
        $localData = 1;
        $remoteData = 1;

        $this->localCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn($localData);

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn(\hash('sha256', (string)$remoteData));

        $this->assertEquals($localData, $this->remoteSyncCacheInstance->load(1));
    }

    /**
     * Test that load will return stale cache.
     *
     * @return void
     */
    public function testLoadWithStaleCache(): void
    {
        $localData = 1;

        $this->localCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn($localData);

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn(false);

        $closure = \Closure::bind(function ($cacheInstance) {
            $cacheInstance->_options['use_stale_cache'] = true;
        }, null, $this->remoteSyncCacheInstance);
        $closure($this->remoteSyncCacheInstance);

        $this->remoteCacheMockExample
            ->expects($this->at(2))
            ->method('load')
            ->willReturn(true);

        $this->assertEquals($localData, $this->remoteSyncCacheInstance->load(1));
    }

    /**
     * Test that load will generate data on the first attempt.
     *
     * @return void
     */
    public function testLoadWithoutStaleCache(): void
    {
        $localData = 1;

        $this->localCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn($localData);

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn(false);

        $closure = \Closure::bind(function ($cacheInstance) {
            $cacheInstance->_options['use_stale_cache'] = true;
        }, null, $this->remoteSyncCacheInstance);
        $closure($this->remoteSyncCacheInstance);

        $this->remoteCacheMockExample
            ->expects($this->at(2))
            ->method('load')
            ->willReturn(false);

        $closure = \Closure::bind(function ($cacheInstance) {
            return $cacheInstance->lockSign;
        }, null, $this->remoteSyncCacheInstance);
        $lockSign = $closure($this->remoteSyncCacheInstance);

        $this->remoteCacheMockExample
            ->expects($this->at(4))
            ->method('load')
            ->willReturn($lockSign);

        $this->assertEquals(false, $this->remoteSyncCacheInstance->load(1));
    }

    /**
     * Test data remove.
     *
     * @return void
     */
    public function testRemove(): void
    {
        $this->remoteCacheMockExample
            ->expects($this->exactly(2))
            ->method('remove')
            ->willReturn(true);

        $this->localCacheMockExample
            ->expects($this->exactly(1))
            ->method('remove')
            ->willReturn(true);

        $this->remoteSyncCacheInstance->remove(1);
    }

    /**
     * Test data clean.
     *
     * @return void
     */
    public function testClean(): void
    {
        $this->remoteCacheMockExample
            ->expects($this->exactly(1))
            ->method('clean')
            ->willReturn(true);

        $this->remoteSyncCacheInstance->clean();
    }

    /**
     * Test data save when remote data exist.
     *
     * @return void
     */
    public function testSaveWithEqualRemoteData(): void
    {
        $remoteData = 1;

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn(\hash('sha256', (string)$remoteData));

        $this->remoteCacheMockExample
            ->expects($this->at(1))
            ->method('load')
            ->willReturn($remoteData);

        $this->localCacheMockExample
            ->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $this->remoteSyncCacheInstance->save($remoteData, 1);
    }

    public function testSaveWithMismatchedRemoteData()
    {
        $remoteData = '1';

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn(\hash('sha256', $remoteData));

        $this->remoteCacheMockExample->expects($this->exactly(2))->method('save');
        $this->localCacheMockExample->expects($this->once())->method('save');

        $this->remoteSyncCacheInstance->save(2, 1);
    }

    /**
     * Test data save when remote data is not exist.
     *
     * @return void
     */
    public function testSaveWithoutRemoteData(): void
    {
        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn(false);

        $this->remoteCacheMockExample->expects($this->exactly(2))->method('save');
        $this->localCacheMockExample->expects($this->once())->method('save');

        $this->remoteSyncCacheInstance->save(1, 1);
    }
}
