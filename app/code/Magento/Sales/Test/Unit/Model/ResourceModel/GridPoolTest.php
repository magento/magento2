<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\ResourceModel;

/**
 * Class GridPoolTest
 */
class GridPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\GridPool
     */
    protected $gridPool;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Grid|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderGridMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Grid|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $invoiceGridMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Grid|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shipmentGridMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid|\PHPUnit\Framework\MockObject\MockObject
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
        $this->orderGridMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Grid::class);
        $this->invoiceGridMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Grid::class);
        $this->shipmentGridMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Grid::class);
        $this->creditmemoGridMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Grid::class);
        $this->statementMock = $this->getMockForAbstractClass(\Zend_Db_Statement_Interface::class);
        $grids = [
            'order_grid' => $this->orderGridMock,
            'invoice_grid' => $this->invoiceGridMock,
            'shipment_grid' => $this->shipmentGridMock,
            'creditmemo_grid' => $this->creditmemoGridMock
        ];
        $this->gridPool = new \Magento\Sales\Model\ResourceModel\GridPool($grids);
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
            ->with($this->equalTo($orderId), $this->equalTo('sfo.entity_id'))
            ->willReturn($this->statementMock);
        $this->invoiceGridMock->expects($this->once())
            ->method('refresh')
            ->with($this->equalTo($orderId), $this->equalTo('sfo.entity_id'))
            ->willReturn($this->statementMock);
        $this->shipmentGridMock->expects($this->once())
            ->method('refresh')
            ->with($this->equalTo($orderId), $this->equalTo('sfo.entity_id'))
            ->willReturn($this->statementMock);
        $this->creditmemoGridMock->expects($this->once())
            ->method('refresh')
            ->with($this->equalTo($orderId), $this->equalTo('sfo.entity_id'))
            ->willReturn($this->statementMock);
        $this->assertEquals($this->gridPool, $this->gridPool->refreshByOrderId($orderId));
    }
}
