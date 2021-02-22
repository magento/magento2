<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Observer;

/**
 * Class GridSyncInsertObserverTest
 */
class GridSyncInsertObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Observer\GridSyncInsertObserver
     */
    protected $unit;

    /**
     * @var \Magento\Sales\Model\ResourceModel\GridInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $gridAggregatorMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventObserverMock;

    /**
     * @var \Magento\Sales\Model\AbstractModel|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $salesModelMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigurationMock;

    protected function setUp(): void
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
        $this->scopeConfigurationMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
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
