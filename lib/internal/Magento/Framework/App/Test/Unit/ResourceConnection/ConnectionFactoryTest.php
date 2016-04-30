<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->objectManager->getObject(
            'Magento\Framework\App\ResourceConnection\ConnectionFactory',
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $cacheAdapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\DdlCache')
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock = $this->getMockBuilder('Magento\Framework\DB\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $adapterClass = 'Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface';
        $connectionAdapterMock = $this->getMockBuilder($adapterClass)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
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
            ->with('Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface')
            ->will($this->returnValue($connectionAdapterMock));
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap(
                [
                    ['Magento\Framework\DB\LoggerInterface', $loggerMock],
                    ['Magento\Framework\DB\Adapter\DdlCache', $cacheAdapterMock],
                ]
            ));
        $this->assertSame($connectionMock, $this->model->create(['active' => true]));
    }
}
