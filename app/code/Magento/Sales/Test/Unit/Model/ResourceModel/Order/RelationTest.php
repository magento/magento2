<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order;

/**
 * Class RelationTest
 */
class RelationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Relation
     */
    protected $relationProcessor;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Handler\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressHandlerMock;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemRepositoryMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderPaymentResourceMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\History|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $statusHistoryResource;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderPaymentMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderStatusHistoryMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderInvoiceMock;

    protected function setUp()
    {
        $this->addressHandlerMock = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Order\Handler\Address')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'removeEmptyAddresses',
                    'process'
                ]
            )
            ->getMock();
        $this->orderItemRepositoryMock = $this->getMockBuilder('Magento\Sales\Api\OrderItemRepositoryInterface')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMockForAbstractClass();
        $this->orderPaymentResourceMock = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->statusHistoryResource = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Order\Status\History')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getItems',
                    'getPayment',
                    'getStatusHistories',
                    'getRelatedObjects'
                ]
            )
            ->getMock();
        $this->orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setOrderId',
                    'setOrder'
                ]
            )
            ->getMock();
        $this->orderPaymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setParentId',
                    'setOrder'
                ]
            )
            ->getMock();
        $this->orderStatusHistoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setParentId',
                    'setOrder'
                ]
            )
            ->getMock();
        $this->orderStatusHistoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\Status\History')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setParentId',
                    'setOrder'
                ]
            )
            ->getMock();
        $this->orderInvoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setOrder',
                    'save'
                ]
            )
            ->getMock();
        $this->relationProcessor = new \Magento\Sales\Model\ResourceModel\Order\Relation(
            $this->addressHandlerMock,
            $this->orderItemRepositoryMock,
            $this->orderPaymentResourceMock,
            $this->statusHistoryResource
        );
    }

    public function testProcessRelation()
    {
        $this->addressHandlerMock->expects($this->once())
            ->method('removeEmptyAddresses')
            ->with($this->orderMock)
            ->willReturnSelf();
        $this->addressHandlerMock->expects($this->once())
            ->method('process')
            ->with($this->orderMock)
            ->willReturnSelf();
        $this->orderMock->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn([$this->orderItemMock]);
        $this->orderMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn('order-id-value');
        $this->orderItemMock->expects($this->once())
            ->method('setOrderId')
            ->with('order-id-value')
            ->willReturnSelf();
        $this->orderItemMock->expects($this->once())
            ->method('setOrder')
            ->with($this->orderMock)
            ->willReturnSelf();
        $this->orderItemRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderItemMock)
            ->willReturnSelf();
        $this->orderMock->expects($this->exactly(2))
            ->method('getPayment')
            ->willReturn($this->orderPaymentMock);
        $this->orderPaymentMock->expects($this->once())
            ->method('setParentId')
            ->with('order-id-value')
            ->willReturnSelf();
        $this->orderPaymentMock->expects($this->once())
            ->method('setOrder')
            ->with($this->orderMock)
            ->willReturnSelf();
        $this->orderPaymentResourceMock->expects($this->once())
            ->method('save')
            ->with($this->orderPaymentMock)
            ->willReturnSelf();
        $this->orderMock->expects($this->exactly(2))
            ->method('getStatusHistories')
            ->willReturn([$this->orderStatusHistoryMock]);
        $this->orderStatusHistoryMock->expects($this->once())
            ->method('setParentId')
            ->with('order-id-value')
            ->willReturnSelf();
        $this->orderStatusHistoryMock->expects($this->once())
            ->method('setOrder')
            ->with($this->orderMock)
            ->willReturnSelf();
        $this->statusHistoryResource->expects($this->once())
            ->method('save')
            ->with($this->orderStatusHistoryMock)
            ->willReturnSelf();
        $this->orderMock->expects($this->exactly(2))
            ->method('getRelatedObjects')
            ->willReturn([$this->orderInvoiceMock]);
        $this->orderInvoiceMock->expects($this->once())
            ->method('setOrder')
            ->with($this->orderMock)
            ->willReturnSelf();
        $this->orderInvoiceMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->relationProcessor->processRelation($this->orderMock);
    }
}
