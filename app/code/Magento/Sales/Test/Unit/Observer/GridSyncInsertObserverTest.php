<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Observer;

/**
 * Class GridSyncInsertObserverTest
 */
class GridSyncInsertObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Observer\GridSyncInsertObserver
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

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigurationMock;

    protected function setUp()
    {
        $this->gridAggregatorMock = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\GridInterface')
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
        $this->scopeConfigurationMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();

        $this->unit = new \Magento\Sales\Observer\GridSyncInsertObserver(
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
        $this->unit->execute($this->eventObserverMock);
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
        $this->unit->execute($this->eventObserverMock);
    }
}
