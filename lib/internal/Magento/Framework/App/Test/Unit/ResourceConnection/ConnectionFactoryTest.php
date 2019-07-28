<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection;

class ConnectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection\ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionFactory = $this->objectManager->getObject(
            \Magento\Framework\App\ResourceConnection\ConnectionFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $cacheAdapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\DdlCache::class)
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
            ->will($this->returnValue($connectionMock));
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface::class)
            ->will($this->returnValue($connectionAdapterMock));
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Framework\DB\Adapter\DdlCache::class)
            ->willReturn($cacheAdapterMock);
        $this->assertSame($connectionMock, $this->connectionFactory->create(['active' => true]));
    }
}
