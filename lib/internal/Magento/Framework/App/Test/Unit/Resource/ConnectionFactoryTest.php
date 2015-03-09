<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Resource;

use Magento\Framework\DB\Adapter\DdlCache;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Resource\ConnectionFactory
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
            'Magento\Framework\App\Resource\ConnectionFactory',
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreateNull()
    {
        $this->objectManagerMock->expects($this->never())
            ->method('get');
        $this->assertNull($this->model->create([]));
        $this->assertNull($this->model->create(['something']));
        $this->assertNull($this->model->create(['active' => null]));
    }

    public function testCreate()
    {
        $cacheAdapterMock = $this->getMockBuilder('Magento\Framework\Cache\FrontendInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock = $this->getMockBuilder('Magento\Framework\DB\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionAdapterMock = $this->getMockBuilder('Magento\Framework\App\Resource\ConnectionAdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $adapterInstanceMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $adapterInstanceMock->expects($this->once())
            ->method('setCacheAdapter')
            ->with($cacheAdapterMock)
            ->willReturnSelf();
        $connectionAdapterMock->expects($this->once())
            ->method('getConnection')
            ->with($loggerMock)
            ->will($this->returnValue($adapterInstanceMock));
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\App\Resource\ConnectionAdapterInterface')
            ->will($this->returnValue($connectionAdapterMock));
        $poolMock = $this->getMockBuilder('Magento\Framework\App\Cache\Type\FrontendPool')
            ->disableOriginalConstructor()
            ->getMock();
        $poolMock->expects($this->once())
            ->method('get')
            ->with(DdlCache::TYPE_IDENTIFIER)
            ->will($this->returnValue($cacheAdapterMock));
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap(
                [
                    ['Magento\Framework\DB\LoggerInterface', $loggerMock],
                    ['Magento\Framework\App\Cache\Type\FrontendPool', $poolMock],
                ]
            ));
        $this->assertSame($adapterInstanceMock, $this->model->create(['active' => true]));
    }
}
