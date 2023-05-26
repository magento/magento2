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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoteSynchronizedCacheTest extends TestCase
{
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->localCacheMockExample = $this->createMock(\Cm_Cache_Backend_File::class);
        $this->remoteCacheMockExample = $this->createMock(Database::class);
        $this->remoteSyncCacheInstance = new RemoteSynchronizedCache(
            [
                'remote_backend' => $this->remoteCacheMockExample,
                'local_backend' => $this->localCacheMockExample
            ]
        );
    }

    /**
     * Test that exception is thrown if cache is not configured.
     *
     * @param array $options
     * @return void
     * @dataProvider initializeWithExceptionDataProvider
     */
    public function testInitializeWithException($options): void
    {
        $this->expectException('Zend_Cache_Exception');
        new RemoteSynchronizedCache($options);
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
                    'local_backend' => null
                ]
            ],
            'empty_remote_backend_option' => [
                'options' => [
                    'remote_backend' => Database::class,
                    'local_backend' => null
                ]
            ],
            'empty_local_backend_option' => [
                'options' => [
                    'remote_backend' => null,
                    'local_backend' => \Cm_Cache_Backend_File::class
                ]
            ]
        ];
    }

    /**
     * Test that exception is not thrown if cache is configured.
     *
     * @param array $options
     *
     * @return void
     * @dataProvider initializeWithOutExceptionDataProvider
     */
    public function testInitializeWithOutException($options): void
    {
        $result = new RemoteSynchronizedCache($options);
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
                        'adapter' => $connectionMock
                    ],
                    'local_backend' => \Cm_Cache_Backend_File::class,
                    'local_backend_options' => [
                        'cache_dir' => '/tmp'
                    ]
                ]
            ]
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
            ->method('load')
            ->willReturn($localData);

        $this->remoteCacheMockExample
            ->method('load')
            ->willReturnOnConsecutiveCalls(\hash('sha256', (string)$remoteData), $remoteData);

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
            ->method('load')
            ->willReturn($localData);

        $this->remoteCacheMockExample
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
            ->method('load')
            ->willReturn($localData);

        $this->remoteCacheMockExample
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
            ->method('load')
            ->willReturn($localData);

        $closure = \Closure::bind(function ($cacheInstance) {
            $cacheInstance->_options['use_stale_cache'] = true;
        }, null, $this->remoteSyncCacheInstance);
        $closure($this->remoteSyncCacheInstance);

        $this->remoteCacheMockExample
            ->method('load')
            ->willReturnOnConsecutiveCalls(false, true);

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
            ->method('load')
            ->willReturnOnConsecutiveCalls($localData);

        $closure = \Closure::bind(function ($cacheInstance) {
            $cacheInstance->_options['use_stale_cache'] = true;
        }, null, $this->remoteSyncCacheInstance);
        $closure($this->remoteSyncCacheInstance);

        $closure = \Closure::bind(function ($cacheInstance) {
            return $cacheInstance->lockSign;
        }, null, $this->remoteSyncCacheInstance);
        $lockSign = $closure($this->remoteSyncCacheInstance);

        $this->remoteCacheMockExample
            ->method('load')
            ->willReturnOnConsecutiveCalls(null, false, false, $lockSign);

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
        $mode = 'clean_tags';
        $tags = ['MAGE'];
        $this->remoteCacheMockExample
            ->expects($this->exactly(1))
            ->method('clean')
            ->with($mode, $tags)
            ->willReturn(true);

        $this->localCacheMockExample
            ->expects($this->once())
            ->method('clean')
            ->with($mode, [])
            ->willReturn(true);

        $this->remoteSyncCacheInstance->clean($mode, $tags);
    }

    /**
     * Test data save when remote data exist.
     *
     * @return void
     */
    public function testSaveWithEqualRemoteData(): void
    {
        $remoteData = 1;
        $tags = ['MAGE'];

        $this->remoteCacheMockExample
            ->method('load')
            ->willReturnOnConsecutiveCalls(\hash('sha256', (string)$remoteData), $remoteData);

        $this->localCacheMockExample
            ->expects($this->once())
            ->method('save')
            ->with($remoteData, 1, [])
            ->willReturn(true);

        $this->remoteSyncCacheInstance->save($remoteData, 1, $tags);
    }

    /**
     * Test data save when remote data are missed but hash exists.
     *
     * @return void
     */
    public function testSaveWithEqualHashesAndMissedRemoteData(): void
    {
        $cacheKey = 'key';
        $dataToSave = '2';
        $remoteData = '1';
        $tags = ['MAGE'];

        $this->remoteCacheMockExample
            ->method('load')
            ->willReturnOnConsecutiveCalls(\hash('sha256', $dataToSave), $remoteData);

        $this->remoteCacheMockExample
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$dataToSave, $cacheKey, $tags],
                [\hash('sha256', $dataToSave), $cacheKey . ':hash', $tags]
            )->willReturn(true);
        $this->localCacheMockExample
            ->expects($this->once())
            ->method('save')
            ->with($dataToSave, $cacheKey, [])
            ->willReturn(true);

        $this->remoteSyncCacheInstance->save($dataToSave, $cacheKey, $tags);
    }

    /**
     * @return void
     */
    public function testSaveWithMismatchedRemoteData(): void
    {
        $remoteData = '1';

        $this->remoteCacheMockExample
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
            ->method('load')
            ->willReturn(false);

        $this->remoteCacheMockExample->expects($this->exactly(2))->method('save');
        $this->localCacheMockExample->expects($this->once())->method('save');

        $this->remoteSyncCacheInstance->save(1, 1);
    }
}
