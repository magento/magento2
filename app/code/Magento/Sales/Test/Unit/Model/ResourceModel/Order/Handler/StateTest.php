<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;

/**
 * Class StateTest
 */
class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Handler\State
     */
    protected $state;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    protected function setUp()
    {
        $this->orderMock = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            [
                '__wakeup',
                'getId',
                'hasCustomerNoteNotify',
                'getCustomerNoteNotify',
                'isCanceled',
                'canUnhold',
                'canInvoice',
                'canShip',
                'getBaseGrandTotal',
                'canCreditmemo',
                'getTotalRefunded',
                'hasForcedCanCreditmemo',
                'getIsInProcess',
                'getConfig',
            ]
        );
        $this->orderMock->expects($this->any())
            ->method('getConfig')
            ->willReturnSelf();
        $this->addressMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);
        $this->addressCollectionMock = $this->createMock(
            \Magento\Sales\Model\ResourceModel\Order\Address\Collection::class
        );
        $this->state = new \Magento\Sales\Model\ResourceModel\Order\Handler\State();
    }

    /**
     * @param bool $isCanceled
     * @param bool $canUnhold
     * @param bool $canInvoice
     * @param bool $canShip
     * @param int $callCanSkipNum
     * @param bool $canCreditmemo
     * @param int $callCanCreditmemoNum
     * @param string $currentState
     * @param string $expectedState
     * @param int $callSetStateNum
     * @dataProvider stateCheckDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testCheck(
        bool $canCreditmemo,
        int $callCanCreditmemoNum,
        bool $canShip,
        int $callCanSkipNum,
        string $currentState,
        string $expectedState = '',
        bool $isInProcess = false,
        int $callGetIsInProcessNum = 0,
        bool $isCanceled = false,
        bool $canUnhold = false,
        bool $canInvoice = false
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
            ->willReturn($canInvoice);
        $this->orderMock->expects($this->exactly($callCanSkipNum))
            ->method('canShip')
            ->willReturn($canShip);
        $this->orderMock->expects($this->exactly($callCanCreditmemoNum))
            ->method('canCreditmemo')
            ->willReturn($canCreditmemo);
        $this->orderMock->expects($this->exactly($callGetIsInProcessNum))
            ->method('getIsInProcess')
            ->willReturn($isInProcess);
        $this->state->check($this->orderMock);
        $this->assertEquals($expectedState, $this->orderMock->getState());
    }

    public function stateCheckDataProvider()
    {
        return [
            'processing - !canCreditmemo!canShip -> closed' =>
                [false, 1, false, 1, Order::STATE_PROCESSING, Order::STATE_CLOSED],
            'complete - !canCreditmemo,!canShip -> closed' =>
                [false, 1, false, 1, Order::STATE_COMPLETE, Order::STATE_CLOSED],
            'processing - !canCreditmemo,canShip -> processing' =>
                [false, 1, true, 2, Order::STATE_PROCESSING, Order::STATE_PROCESSING],
            'complete - !canCreditmemo,canShip -> complete' =>
                [false, 1, true, 1, Order::STATE_COMPLETE, Order::STATE_COMPLETE],
            'processing - canCreditmemo,!canShip -> complete' =>
                [true, 1, false, 1, Order::STATE_PROCESSING, Order::STATE_COMPLETE],
            'complete - canCreditmemo,!canShip -> complete' =>
                [true, 1, false, 0, Order::STATE_COMPLETE, Order::STATE_COMPLETE],
            'processing - canCreditmemo, canShip -> processing' =>
                [true, 1, true, 1, Order::STATE_PROCESSING, Order::STATE_PROCESSING],
            'complete - canCreditmemo, canShip -> complete' =>
                [true, 1, true, 0, Order::STATE_COMPLETE, Order::STATE_COMPLETE],
            'new - canCreditmemo, canShip, IsInProcess -> processing' =>
                [true, 1, true, 1, Order::STATE_NEW, Order::STATE_PROCESSING, true, 1],
            'new - canCreditmemo, !canShip, IsInProcess -> processing' =>
                [true, 1, false, 1, Order::STATE_NEW, Order::STATE_COMPLETE, true, 1],
            'new - canCreditmemo, canShip, !IsInProcess -> new' =>
                [true, 0, true, 0, Order::STATE_NEW, Order::STATE_NEW, false, 1],
            'hold - canUnhold -> hold' =>
                [true, 0, true, 0, Order::STATE_HOLDED, Order::STATE_HOLDED, false, 0, false, true],
            'payment_review - canUnhold -> payment_review' =>
                [true, 0, true, 0, Order::STATE_PAYMENT_REVIEW, Order::STATE_PAYMENT_REVIEW, false, 0, false, true],
            'pending_payment - canUnhold -> pending_payment' =>
                [true, 0, true, 0, Order::STATE_PENDING_PAYMENT, Order::STATE_PENDING_PAYMENT, false, 0, false, true],
            'cancelled - isCanceled -> cancelled' =>
                [true, 0, true, 0, Order::STATE_HOLDED, Order::STATE_HOLDED, false, 0, true],
        ];
    }
}
