<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource;

/**
 * Class GridPoolTest
 */
class GridPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\GridPool
     */
    protected $gridPool;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderGridMock;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Invoice\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceGridMock;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentGridMock;
    /**
     * @var \Magento\Sales\Model\Resource\Order\Creditmemo\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoGridMock;
    /**
     * @var \Zend_Db_Statement_Interface
     */
    protected $statementMock;

    /**
     * Prepare mock objects
     */
    public function setUp()
    {
        $this->orderGridMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Grid', [], [], '', false
        );
        $this->invoiceGridMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Invoice\Grid', [], [], '', false
        );
        $this->shipmentGridMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Shipment\Grid', [], [], '', false
        );
        $this->creditmemoGridMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Creditmemo\Grid', [], [], '', false
        );
        $this->statementMock = $this->getMockForAbstractClass('Zend_Db_Statement_Interface');
        $this->gridPool = new \Magento\Sales\Model\Resource\GridPool(
            $this->orderGridMock, $this->invoiceGridMock, $this->shipmentGridMock, $this->creditmemoGridMock
        );
    }

    /**
     * Test method refreshByOrderId()
     */
    public function testRefreshByOrderId()
    {
        $orderId = 1;
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
