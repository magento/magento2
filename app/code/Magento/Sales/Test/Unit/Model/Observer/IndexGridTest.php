<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Observer;

/**
 * Class IndexGridTest
 */
class IndexGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Observer\IndexGrid
     */
    protected $indexGrid;

    /**
     * @var \Magento\Sales\Model\Resource\GridInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gridAggregatorMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigurationMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserverMock;

    /**
     * @var \Magento\Sales\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesModelMock;

    public function setUp()
    {
        $this->gridAggregatorMock = $this->getMockBuilder('Magento\Sales\Model\Resource\GridInterface')
            ->getMockForAbstractClass();
        $this->scopeConfigurationMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();
        $this->eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getObject',
                    'getDataObject'
                ]
            )
            ->getMock();
        $this->salesModelMock = $this->getMockBuilder('Magento\Sales\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId'
                ]
            )
            ->getMockForAbstractClass();
        $this->indexGrid = new \Magento\Sales\Model\Observer\IndexGrid(
            $this->gridAggregatorMock,
            $this->scopeConfigurationMock
        );
    }

    public function testSyncInsert()
    {
        $this->eventObserverMock->expects($this->once())
            ->method('getObject')
            ->willReturn($this->salesModelMock);
        $this->salesModelMock->expects($this->once())
            ->method('getId')
            ->willReturn('sales-id-value');
        $this->scopeConfigurationMock->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing', 'default', null)
            ->willReturn(false);
        $this->gridAggregatorMock->expects($this->once())
            ->method('refresh')
            ->with('sales-id-value');
        $this->indexGrid->syncInsert($this->eventObserverMock);
    }

    public function testSyncInsertDisabled()
    {
        $this->scopeConfigurationMock->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing', 'default', null)
            ->willReturn(true);
        $this->gridAggregatorMock->expects($this->never())
            ->method('refresh')
            ->with('sales-id-value');
        $this->indexGrid->syncInsert($this->eventObserverMock);
    }

    public function testSyncRemove()
    {
        $this->eventObserverMock->expects($this->once())
            ->method('getDataObject')
            ->willReturn($this->salesModelMock);
        $this->salesModelMock->expects($this->once())
            ->method('getId')
            ->willReturn('sales-id-value');
        $this->gridAggregatorMock->expects($this->once())
            ->method('purge')
            ->with('sales-id-value');
        $this->indexGrid->syncRemove($this->eventObserverMock);
    }

    public function testAsyncInsert()
    {
        $this->scopeConfigurationMock->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing', 'default', null)
            ->willReturn(true);
        $this->gridAggregatorMock->expects($this->once())
            ->method('refreshBySchedule');
        $this->indexGrid->asyncInsert();
    }

    public function testAsyncInsertDisabled()
    {
        $this->scopeConfigurationMock->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing', 'default', null)
            ->willReturn(false);
        $this->gridAggregatorMock->expects($this->never())
            ->method('refreshBySchedule');
        $this->indexGrid->asyncInsert();
    }
}
