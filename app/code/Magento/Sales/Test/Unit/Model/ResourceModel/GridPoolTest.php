<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\ResourceModel;

/**
 * Class GridPoolTest
 */
class GridPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\GridPool
     */
    protected $gridPool;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderGridMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceGridMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentGridMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoGridMock;
    /**
     * @var \Zend_Db_Statement_Interface
     */
    protected $statementMock;

    /**
     * Prepare mock objects
     */
    protected function setUp()
    {
        $this->orderGridMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Grid::class, [], [], '', false
        );
        $this->invoiceGridMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Grid::class, [], [], '', false
        );
        $this->shipmentGridMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Grid::class, [], [], '', false
        );
        $this->creditmemoGridMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Grid::class, [], [], '', false
        );
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
            ->will($this->returnValue($this->statementMock));
        $this->invoiceGridMock->expects($this->once())
            ->method('refresh')
            ->with($this->equalTo($orderId), $this->equalTo('sfo.entity_id'))
            ->will($this->returnValue($this->statementMock));
        $this->shipmentGridMock->expects($this->once())
            ->method('refresh')
            ->with($this->equalTo($orderId), $this->equalTo('sfo.entity_id'))
            ->will($this->returnValue($this->statementMock));
        $this->creditmemoGridMock->expects($this->once())
            ->method('refresh')
            ->with($this->equalTo($orderId), $this->equalTo('sfo.entity_id'))
            ->will($this->returnValue($this->statementMock));
        $this->assertEquals($this->gridPool, $this->gridPool->refreshByOrderId($orderId));
    }
}
