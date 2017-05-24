<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Observer;

/**
 * Class GridSyncRemoveObserverTest
 */
class GridSyncRemoveObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Observer\GridSyncRemoveObserver
     */
    protected $unit;

    /**
     * @var \Magento\Sales\Model\ResourceModel\GridInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gridAggregatorMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserverMock;

    /**
     * @var \Magento\Sales\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesModelMock;

    protected function setUp()
    {
        $this->gridAggregatorMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\GridInterface::class)
            ->getMockForAbstractClass();
        $this->eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getObject',
                    'getDataObject'
                ]
            )
            ->getMock();
        $this->salesModelMock = $this->getMockBuilder(\Magento\Sales\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId'
                ]
            )
            ->getMockForAbstractClass();
        $this->unit = new \Magento\Sales\Observer\GridSyncRemoveObserver(
            $this->gridAggregatorMock
        );
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
        $this->unit->execute($this->eventObserverMock);
    }
}
