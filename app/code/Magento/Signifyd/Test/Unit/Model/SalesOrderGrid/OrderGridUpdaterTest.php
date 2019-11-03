<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\SalesOrderGrid;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\ResourceModel\GridInterface;
use Magento\Signifyd\Model\SalesOrderGrid\OrderGridUpdater;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class OrderGridUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GridInterface|MockObject
     */
    private $orderGrid;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $globalConfig;

    /**
     * @var OrderGridUpdater
     */
    private $model;

    /**
     * Sets up testing class and dependency mocks.
     */
    protected function setUp()
    {
        $this->orderGrid = $this->getMockBuilder(GridInterface::class)
            ->getMockForAbstractClass();
        $this->globalConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->model = new OrderGridUpdater($this->orderGrid, $this->globalConfig);
    }

    public function testUpdateInSyncMode()
    {
        $orderId = 1;

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing', 'default', null)
            ->willReturn(false);
        $this->orderGrid->expects($this->once())
            ->method('refresh')
            ->with($orderId);

        $this->model->update($orderId);
    }

    public function testUpdateInAsyncMode()
    {
        $orderId = 1;

        $this->globalConfig->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing', 'default', null)
            ->willReturn(true);
        $this->orderGrid->expects($this->never())
            ->method('refresh')
            ->with($orderId);

        $this->model->update($orderId);
    }
}
