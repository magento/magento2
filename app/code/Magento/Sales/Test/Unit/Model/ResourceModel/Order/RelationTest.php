<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order;

use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Sales\Model\Order\Status\History as StatusHistory;
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
        $this->addressHandlerMock = $this->createPartialMock(
            Address::class,
            ['removeEmptyAddresses', 'process']
        );
        $this->orderItemRepositoryMock = $this->createMock(OrderItemRepositoryInterface::class);
        $this->orderPaymentResourceMock = $this->createPartialMock(Payment::class, ['save']);
        $this->statusHistoryResource = $this->createPartialMock(History::class, ['save']);
        $this->orderMock = $this->createPartialMock(
            Order::class,
            ['getId', 'getItems', 'getPayment', 'getStatusHistories', 'getRelatedObjects']
        );
        $this->orderItemMock = $this->createPartialMock(Item::class, ['setOrderId', 'setOrder']);
        $this->orderPaymentMock = $this->createPartialMock(
            OrderPayment::class,
            ['setParentId', 'setOrder']
        );
        $this->orderStatusHistoryMock = $this->createPartialMock(
            StatusHistory::class,
            ['setParentId', 'setOrder']
        );
        $this->orderInvoiceMock = $this->createPartialMock(Invoice::class, ['setOrder', 'save']);
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
