<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\ResourceModel\GridInterface;
use Magento\Sales\Observer\GridSyncInsertObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridSyncInsertObserverTest extends TestCase
{
    /**
     * @var GridSyncInsertObserver
     */
    protected $unit;

    /**
     * @var GridInterface|MockObject
     */
    protected $gridAggregatorMock;

    /**
     * @var Observer|MockObject
     */
    protected $eventObserverMock;

    /**
     * @var AbstractModel|MockObject
     */
    protected $salesModelMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigurationMock;

    protected function setUp(): void
    {
        $this->gridAggregatorMock = $this->getMockBuilder(GridInterface::class)
            ->getMockForAbstractClass();
        $this->eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getObject',
                    'getDataObject'
                ]
            )
            ->getMock();
        $this->salesModelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getId'
                ]
            )
            ->getMockForAbstractClass();
        $this->scopeConfigurationMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->unit = new GridSyncInsertObserver(
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
