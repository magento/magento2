<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Test\Unit\Backend;

use Magento\Framework\Cache\Backend\Database;
use Magento\Framework\Cache\Backend\RemoteSynchronizedCache;

class RemoteSynchronizedCacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Cm_Cache_Backend_File|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localCacheMockExample;

    /**
     * @var Database|\PHPUnit\Framework\MockObject\MockObject
     */
    private $remoteCacheMockExample;

    /**
     * @var RemoteSynchronizedCache
     */
    private $remoteSyncCacheInstance;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->localCacheMockExample = $this->getMockBuilder(\Cm_Cache_Backend_File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->remoteCacheMockExample = $this->getMockBuilder(\Magento\Framework\Cache\Backend\Database::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Framework\Cache\Backend\Database $databaseCacheInstance */

        $this->remoteSyncCacheInstance = $this->objectManager->getObject(
            \Magento\Framework\Cache\Backend\RemoteSynchronizedCache::class,
            [
                'options' => [
                    'remote_backend' => $this->remoteCacheMockExample,
                    'local_backend' => $this->localCacheMockExample,
                ],
            ]
        );
    }

    /**
     * @param array $options
     *
     * @dataProvider initializeWithExceptionDataProvider
     */
    public function testInitializeWithException($options)
    {
        $this->expectException(\Zend_Cache_Exception::class);

        $this->objectManager->getObject(
            \Magento\Framework\Cache\Backend\RemoteSynchronizedCache::class,
            [
                'options' => $options,
            ]
        );
    }

    /**
     * @return array
     */
    public function initializeWithExceptionDataProvider()
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
                    'remote_backend' => \Magento\Framework\Cache\Backend\Database::class,
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
     * @param array $options
     *
     * @dataProvider initializeWithOutExceptionDataProvider
     */
    public function testInitializeWithOutException($options)
    {
        $result = $this->objectManager->getObject(
            \Magento\Framework\Cache\Backend\RemoteSynchronizedCache::class,
            [
                'options' => $options,
            ]
        );
        $this->assertInstanceOf(\Magento\Framework\Cache\Backend\RemoteSynchronizedCache::class, $result);
    }

    /**
     * @return array
     */
    public function initializeWithOutExceptionDataProvider()
    {
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'not_empty_backend_option' => [
                'options' => [
                    'remote_backend' => \Magento\Framework\Cache\Backend\Database::class,
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
     * Test that load will always return newest data.
     */
    public function testLoadWithLocalData()
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
            ->willReturn(\hash('sha256', $remoteData));

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

    public function testLoadWithNoLocalAndNoRemoteData()
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

        $this->assertEquals($remoteData, $this->remoteSyncCacheInstance->load(1));
    }

    public function testLoadWithNoLocalAndRemoteData()
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

    public function testRemove()
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

    public function testClean()
    {
        $this->remoteCacheMockExample
            ->expects($this->exactly(1))
            ->method('clean')
            ->willReturn(true);

        $this->remoteSyncCacheInstance->clean();
    }

    public function testSaveWithEqualRemoteData()
    {
        $remoteData = 1;

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn(\hash('sha256', $remoteData));

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
        $remoteData = 1;

        $this->remoteCacheMockExample
            ->expects($this->at(0))
            ->method('load')
            ->willReturn(\hash('sha256', $remoteData));

        $this->remoteCacheMockExample->expects($this->exactly(2))->method('save');
        $this->localCacheMockExample->expects($this->once())->method('save');

        $this->remoteSyncCacheInstance->save(2, 1);
    }

    public function testSaveWithoutRemoteData()
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
