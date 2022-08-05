<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Magento\Sales\Model\ResourceModel\Grid;
use Magento\Sales\Model\ResourceModel\GridPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GridPoolTest extends TestCase
{
    /**
     * @var GridPool
     */
    protected $gridPool;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Grid|MockObject
     */
    protected $orderGridMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Grid|MockObject
     */
    protected $invoiceGridMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Grid|MockObject
     */
    protected $shipmentGridMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid|MockObject
     */
    protected $creditmemoGridMock;
    /**
     * @var \Zend_Db_Statement_Interface
     */
    protected $statementMock;

    /**
     * Prepare mock objects
     */
    protected function setUp(): void
    {
        $this->orderGridMock = $this->createMock(Grid::class);
        $this->invoiceGridMock = $this->createMock(Grid::class);
        $this->shipmentGridMock = $this->createMock(Grid::class);
        $this->creditmemoGridMock = $this->createMock(Grid::class);
        $this->statementMock = $this->getMockForAbstractClass(\Zend_Db_Statement_Interface::class);
        $grids = [
            'order_grid' => $this->orderGridMock,
            'invoice_grid' => $this->invoiceGridMock,
            'shipment_grid' => $this->shipmentGridMock,
            'creditmemo_grid' => $this->creditmemoGridMock
        ];
        $this->gridPool = new GridPool($grids);
    }

    /**
     * Test method refreshByOrderId()
     */
    public function testRefreshByOrderId()
    {
        $orderId = 1;

        $this->orderGridMock->expects($this->once())
            ->method('getOrderIdField')
            ->willReturn('sfo.entity_id');
        $this->invoiceGridMock->expects($this->once())
            ->method('getOrderIdField')
            ->willReturn('sfo.entity_id');
        $this->shipmentGridMock->expects($this->once())
            ->method('getOrderIdField')
            ->willReturn('sfo.entity_id');
        $this->creditmemoGridMock->expects($this->once())
            ->method('getOrderIdField')
            ->willReturn('sfo.entity_id');

        $this->orderGridMock->expects($this->once())
            ->method('refresh')
            ->with($orderId, 'sfo.entity_id')
            ->willReturn($this->statementMock);
        $this->invoiceGridMock->expects($this->once())
            ->method('refresh')
            ->with($orderId, 'sfo.entity_id')
            ->willReturn($this->statementMock);
        $this->shipmentGridMock->expects($this->once())
            ->method('refresh')
            ->with($orderId, 'sfo.entity_id')
            ->willReturn($this->statementMock);
        $this->creditmemoGridMock->expects($this->once())
            ->method('refresh')
            ->with($orderId, 'sfo.entity_id')
            ->willReturn($this->statementMock);
        $this->assertEquals($this->gridPool, $this->gridPool->refreshByOrderId($orderId));
    }
}
