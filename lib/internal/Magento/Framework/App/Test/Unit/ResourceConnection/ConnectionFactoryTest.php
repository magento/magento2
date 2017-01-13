<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection\ConnectionFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManager->getObject(
            \Magento\Framework\App\ResourceConnection\ConnectionFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $cacheAdapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\DdlCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock = $this->getMockBuilder(\Magento\Framework\DB\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterClass = \Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface::class;
        $connectionAdapterMock = $this->getMockBuilder($adapterClass)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock->expects($this->once())
            ->method('setCacheAdapter')
            ->with($cacheAdapterMock)
            ->willReturnSelf();
        $connectionAdapterMock->expects($this->once())
            ->method('getConnection')
            ->with($loggerMock)
            ->will($this->returnValue($connectionMock));
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface::class)
            ->will($this->returnValue($connectionAdapterMock));
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap(
                [
                    [\Magento\Framework\DB\LoggerInterface::class, $loggerMock],
                    [\Magento\Framework\DB\Adapter\DdlCache::class, $cacheAdapterMock],
                ]
            ));
        $this->assertSame($connectionMock, $this->model->create(['active' => true]));
    }
}
