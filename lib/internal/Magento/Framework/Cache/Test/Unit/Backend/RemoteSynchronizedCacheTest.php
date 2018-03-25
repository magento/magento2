<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Test\Unit\Backend;

class RemoteSynchronizedCacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @param array $options
     *
     * @expectedException \Zend_Cache_Exception
     * @dataProvider initializeWithExceptionDataProvider
     */
    public function testInitializeWithException($options)
    {
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
     * @param array $options
     * @param bool|string $expected
     *
     * @dataProvider loadDataProvider
     */
    public function testLoad($options, $expected)
    {
        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            \Magento\Framework\Cache\Backend\RemoteSynchronizedCache::class,
            [
                'options' => $options,
            ]
        );

        $this->assertEquals($expected, $database->load(5));
    }

    /**
     * @return array
     */
    public function loadDataProvider()
    {
        return [
            'cacheInvalidationTime_is_less_than_that_dataModificationTime' => [
                'options' => [
                    'remote_backend' => $this->getDatabaseMock(444),
                    'local_backend' => $this->getFileMock(555, 'loaded_value'),
                ],
                'expected' => 'loaded_value',
            ],
            'cacheInvalidationTime_is_greater_than_that_dataModificationTime' => [
                'options' => [
                    'remote_backend' => $this->getDatabaseMock(444),
                    'local_backend' => $this->getFileMock(333, 'loaded_value'),
                ],
                'expected' => false,
            ],
            'cacheInvalidationTime_is_equal_to_the_dataModificationTime' => [
                'options' => [
                    'remote_backend' => $this->getDatabaseMock(444),
                    'local_backend' => $this->getFileMock(444, 'loaded_value'),
                ],
                'expected' => 'loaded_value',
            ],
        ];
    }

    /**
     * @param integer $cacheInvalidationTime
     * @return \Magento\Framework\Cache\Backend\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDatabaseMock($cacheInvalidationTime)
    {
        $databaseMock = $this->getMockBuilder(\Magento\Framework\Cache\Backend\Database::class)
            ->setMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $databaseMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($cacheInvalidationTime));

        return $databaseMock;
    }

    /**
     * @param integer $dataModificationTime
     * @return \Cm_Cache_Backend_File|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getFileMock($dataModificationTime, $cacheResult)
    {
        $fileMock = $this->getMockBuilder(\Cm_Cache_Backend_File::class)
            ->setMethods(['test', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileMock->expects($this->once())
            ->method('test')
            ->will($this->returnValue($dataModificationTime));
        $fileMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($cacheResult));

        return $fileMock;
    }

    public function testRemove()
    {
        $databaseMock = $this->getMockBuilder(\Magento\Framework\Cache\Backend\Database::class)
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();
        $databaseMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $fileMock = $this->getMockBuilder(\Cm_Cache_Backend_File::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileMock->expects($this->once())
            ->method('remove')
            ->will($this->returnValue(true));

        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            \Magento\Framework\Cache\Backend\RemoteSynchronizedCache::class,
            [
                'options' => [
                    'remote_backend' => $databaseMock,
                    'local_backend' => $fileMock,
                ]
            ]
        );

        $this->assertEquals(true, $database->remove(5));
    }

    public function testClean()
    {
        $databaseMock = $this->getMockBuilder(\Magento\Framework\Cache\Backend\Database::class)
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();
        $databaseMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $fileMock = $this->getMockBuilder(\Cm_Cache_Backend_File::class)
            ->setMethods(['clean'])
            ->disableOriginalConstructor()
            ->getMock();
        $fileMock->expects($this->once())
            ->method('clean')
            ->will($this->returnValue(true));

        /** @var \Magento\Framework\Cache\Backend\Database $database */
        $database = $this->objectManager->getObject(
            \Magento\Framework\Cache\Backend\RemoteSynchronizedCache::class,
            [
                'options' => [
                    'remote_backend' => $databaseMock,
                    'local_backend' => $fileMock,
                ]
            ]
        );

        $this->assertEquals(true, $database->clean());
    }
}
