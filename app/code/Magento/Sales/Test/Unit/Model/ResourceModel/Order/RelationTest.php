<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order;

use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Handler\Address;
use Magento\Sales\Model\ResourceModel\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Relation;
use Magento\Sales\Model\ResourceModel\Order\Status\History;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RelationTest extends TestCase
{
    /**
     * @var Relation
     */
    protected $relationProcessor;

    /**
     * @var Address|MockObject
     */
    protected $addressHandlerMock;

    /**
     * @var OrderItemRepositoryInterface|MockObject
     */
    protected $orderItemRepositoryMock;

    /**
     * @var Payment|MockObject
     */
    protected $orderPaymentResourceMock;

    /**
     * @var History|MockObject
     */
    protected $statusHistoryResource;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var Item|MockObject
     */
    protected $orderItemMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    protected $orderPaymentMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|MockObject
     */
    protected $orderStatusHistoryMock;

    /**
     * @var Invoice|MockObject
     */
    protected $orderInvoiceMock;

    protected function setUp(): void
    {
        $this->addressHandlerMock = $this->getMockBuilder(
            Address::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['removeEmptyAddresses', 'process'])
            ->getMock();
        $this->orderItemRepositoryMock = $this->getMockBuilder(OrderItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMockForAbstractClass();
        $this->orderPaymentResourceMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $this->statusHistoryResource = $this->getMockBuilder(
            History::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)
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
        $this->orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOrderId', 'setOrder'])
            ->getMock();
        $this->orderPaymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['setParentId', 'setOrder'])
            ->getMock();
        $this->orderStatusHistoryMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['setParentId', 'setOrder'])
            ->getMock();
        $this->orderStatusHistoryMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Status\History::class)
            ->disableOriginalConstructor()
            ->setMethods(['setParentId', 'setOrder'])
            ->getMock();
        $this->orderInvoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOrder', 'save'])
            ->getMock();
        $this->relationProcessor = new Relation(
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
