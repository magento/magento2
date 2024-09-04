<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Handler\State;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->addMethods(['hasCustomerNoteNotify', 'hasForcedCanCreditmemo', 'getIsInProcess'])
            ->onlyMethods(
                [
                    'getId',
                    'getCustomerNoteNotify',
                    'isCanceled',
                    'canUnhold',
                    'canInvoice',
                    'canShip',
                    'getBaseGrandTotal',
                    'canCreditmemo',
                    'getTotalRefunded',
                    'getConfig',
                    'getIsVirtual',
                    'getIsNotVirtual',
                    'getStatus',
                    'getAllItems',
                    'getInvoiceCollection',
                    'getTotalQtyOrdered',
                    'getTotalDue'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->expects($this->any())
            ->method('getConfig')
            ->willReturnSelf();
        $invoice = $this->createMock(Invoice::class);
        $invoice->expects($this->any())
            ->method('getState')
            ->willReturn(Invoice::STATE_PAID);
        $invoiceCollection = $this->createMock(InvoiceCollection::class);
        $invoiceCollection->expects($this->any())
            ->method('getItems')
            ->willReturn([$invoice]);
        $this->orderMock->expects($this->any())
            ->method('getInvoiceCollection')
            ->willReturn($invoiceCollection);
        $this->state = new State();
    }

    /**
     * @param bool $canCreditmemo
     * @param int $callCanCreditmemoNum
     * @param bool $canShip
     * @param int $callCanSkipNum
     * @param string $currentState
     * @param string $expectedState
     * @param bool $isInProcess
     * @param int $callGetIsInProcessNum
     * @param bool $isCanceled
     * @param bool $canUnhold
     * @param bool $isNotVirtual
     * @dataProvider stateCheckDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testCheck(
        bool $canCreditmemo,
        int $callCanCreditmemoNum,
        bool $canShip,
        int $callCanSkipNum,
        string $currentState,
        string $expectedState,
        bool $isInProcess,
        int $callGetIsInProcessNum,
        bool $isCanceled,
        bool $canUnhold,
        bool $isNotVirtual
    ) {
        $this->orderMock->setState($currentState);
        $this->orderMock->expects($this->any())
            ->method('isCanceled')
            ->willReturn($isCanceled);
        $this->orderMock->expects($this->any())
            ->method('canUnhold')
            ->willReturn($canUnhold);
        $this->orderMock->expects($this->any())
            ->method('canInvoice')
            ->willReturn(false);
        $this->orderMock->expects($this->exactly($callCanSkipNum))
            ->method('canShip')
            ->willReturn($canShip);
        $this->orderMock->expects($this->exactly($callCanCreditmemoNum))
            ->method('canCreditmemo')
            ->willReturn($canCreditmemo);
        $this->orderMock->expects($this->exactly($callGetIsInProcessNum))
            ->method('getIsInProcess')
            ->willReturn($isInProcess);
        $this->orderMock->method('getIsNotVirtual')
            ->willReturn($isNotVirtual);
        $shippedItem = $this->createMock(Item::class);
        $shippedItem->expects($this->any())->method('getQtyShipped')->willReturn(1);
        $shippedItem->expects($this->any())->method('getQtyRefunded')->willReturn(1);
        $shippedItem->expects($this->any())->method('getProductType')->willReturn('simple');
        $shippedItem->expects($this->any())->method('canShip')->willReturn(false);
        $shippableItem = $this->createMock(Item::class);
        $shippableItem->expects($this->any())->method('getQtyShipped')->willReturn(0);
        $shippableItem->expects($this->any())->method('getQtyRefunded')->willReturn(0);
        $shippableItem->expects($this->any())->method('getProductType')->willReturn('simple');
        $shippableItem->expects($this->any())->method('canShip')->willReturn(true);
        $this->orderMock->method('getAllItems')
            ->willReturn([$shippedItem, $shippableItem]);
        if (!$isNotVirtual) {
            $this->orderMock->method('getIsVirtual')
                ->willReturn(!$isNotVirtual);
            $this->orderMock->method('getStatus')
                ->willReturn($expectedState);
        }
        $this->orderMock->expects($this->any())->method('getTotalQtyOrdered')->willReturn(2);
        $this->state->check($this->orderMock);
        $this->assertEquals($expectedState, $this->orderMock->getState());
    }

    /**
     * Data provider for testCheck
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public static function stateCheckDataProvider()
    {
        return [
            'processing - partiallyRefundedOrderShipped = true, hasPendingShipmentItems = true -> processing' => [
                'canCreditmemo' => false,
                'callCanCreditmemoNum' => 1,
                'canShip' => true,
                'callCanSkipNum' => 2,
                'currentState' => Order::STATE_PROCESSING,
                'expectedState' => Order::STATE_PROCESSING,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'processing - !canCreditmemo!canShip -> closed' => [
                'canCreditmemo' => false,
                'callCanCreditmemoNum' => 1,
                'canShip' => false,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_PROCESSING,
                'expectedState' => Order::STATE_CLOSED,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'complete - !canCreditmemo,!canShip -> closed' => [
                'canCreditmemo' => false,
                'callCanCreditmemoNum' => 1,
                'canShip' => false,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_COMPLETE,
                'expectedState' => Order::STATE_CLOSED,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'processing - !canCreditmemo,canShip -> processing' => [
                'canCreditmemo' => false,
                'callCanCreditmemoNum' => 1,
                'canShip' => true,
                'callCanSkipNum' => 2,
                'currentState' => Order::STATE_PROCESSING,
                'expectedState' => Order::STATE_PROCESSING,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'complete - !canCreditmemo,canShip -> complete' => [
                'canCreditmemo' => false,
                'callCanCreditmemoNum' => 1,
                'canShip' => true,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_COMPLETE,
                'expectedState' => Order::STATE_COMPLETE,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'processing - canCreditmemo,!canShip -> complete' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 1,
                'canShip' => false,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_PROCESSING,
                'expectedState' => Order::STATE_COMPLETE,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'complete - canCreditmemo,!canShip -> complete' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 1,
                'canShip' => false,
                'callCanSkipNum' => 0,
                'currentState' => Order::STATE_COMPLETE,
                'expectedState' => Order::STATE_COMPLETE,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'processing - canCreditmemo, canShip -> processing' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 1,
                'canShip' => true,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_PROCESSING,
                'expectedState' => Order::STATE_PROCESSING,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'complete - canCreditmemo, canShip -> complete' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 1,
                'canShip' => true,
                'callCanSkipNum' => 0,
                'currentState' => Order::STATE_COMPLETE,
                'expectedState' => Order::STATE_COMPLETE,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'new - canCreditmemo, canShip, IsInProcess -> processing' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 1,
                'canShip' => true,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_NEW,
                'expectedState' => Order::STATE_PROCESSING,
                'isInProcess' => true,
                'callGetIsInProcessNum' => 1,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'new - canCreditmemo, !canShip, IsInProcess -> processing' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 1,
                'canShip' => false,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_NEW,
                'expectedState' => Order::STATE_COMPLETE,
                'isInProcess' => true,
                'callGetIsInProcessNum' => 1,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'new - canCreditmemo, canShip, !IsInProcess -> new' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 0,
                'canShip' => true,
                'callCanSkipNum' => 0,
                'currentState' => Order::STATE_NEW,
                'expectedState' => Order::STATE_NEW,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 1,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'hold - canUnhold -> hold' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 0,
                'canShip' => true,
                'callCanSkipNum' => 0,
                'currentState' => Order::STATE_HOLDED,
                'expectedState' => Order::STATE_HOLDED,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => true,
                'isNotVirtual' => true
            ],
            'payment_review - canUnhold -> payment_review' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 0,
                'canShip' => true,
                'callCanSkipNum' => 0,
                'currentState' => Order::STATE_PAYMENT_REVIEW,
                'expectedState' => Order::STATE_PAYMENT_REVIEW,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => true,
                'isNotVirtual' => true
            ],
            'pending_payment - canUnhold -> pending_payment' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 0,
                'canShip' => true,
                'callCanSkipNum' => 0,
                'currentState' => Order::STATE_PENDING_PAYMENT,
                'expectedState' => Order::STATE_PENDING_PAYMENT,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => true,
                'isNotVirtual' => true
            ],
            'cancelled - isCanceled -> cancelled' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 0,
                'canShip' => true,
                'callCanSkipNum' => 0,
                'currentState' => Order::STATE_HOLDED,
                'expectedState' => Order::STATE_HOLDED,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => true,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'processing - !canCreditmemo!canShip -> complete(virtual product)' => [
                'canCreditmemo' => false,
                'callCanCreditmemoNum' => 1,
                'canShip' => false,
                'callCanSkipNum' => 2,
                'currentState' => Order::STATE_PROCESSING,
                'expectedState' => Order::STATE_COMPLETE,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => false
            ],
            'complete - !canCreditmemo, !canShip - closed(virtual product)' => [
                'canCreditmemo' => false,
                'callCanCreditmemoNum' => 1,
                'canShip' => false,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_COMPLETE,
                'expectedState' => Order::STATE_CLOSED,
                'isInProcess' => false,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => false
            ],
            'processing - canCreditmemo, !canShip, !isPartiallyRefundedOrderShipped -> processing' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 1,
                'canShip' => true,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_PROCESSING,
                'expectedState' => Order::STATE_PROCESSING,
                'isInProcess' => true,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
            'processing - canCreditmemo, !canShip, isPartiallyRefundedOrderShipped -> complete' => [
                'canCreditmemo' => true,
                'callCanCreditmemoNum' => 1,
                'canShip' => false,
                'callCanSkipNum' => 1,
                'currentState' => Order::STATE_PROCESSING,
                'expectedState' => Order::STATE_COMPLETE,
                'isInProcess' => true,
                'callGetIsInProcessNum' => 0,
                'isCanceled' => false,
                'canUnhold' => false,
                'isNotVirtual' => true
            ],
        ];
    }
}
