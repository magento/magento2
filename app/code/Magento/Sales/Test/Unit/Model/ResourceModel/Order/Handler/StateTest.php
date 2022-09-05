<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\ResourceModel\Order\Address\Collection;
use Magento\Sales\Model\ResourceModel\Order\Handler\State;
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
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->expects($this->any())
            ->method('getConfig')
            ->willReturnSelf();
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
        if (!$isNotVirtual) {
            $this->orderMock->method('getIsVirtual')
                ->willReturn(!$isNotVirtual);
            $this->orderMock->method('getStatus')
                ->willReturn($expectedState);
        }
        $this->state->check($this->orderMock);
        $this->assertEquals($expectedState, $this->orderMock->getState());
    }

    /**
     * Data provider for testCheck
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function stateCheckDataProvider()
    {
        return [
            'processing - !canCreditmemo!canShip -> closed' => [
                'can_credit_memo' => false,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_PROCESSING,
                'expected_state' => Order::STATE_CLOSED,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'complete - !canCreditmemo,!canShip -> closed' => [
                'can_credit_memo' => false,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_COMPLETE,
                'expected_state' => Order::STATE_CLOSED,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'processing - !canCreditmemo,canShip -> processing' => [
                'can_credit_memo' => false,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 2,
                'current_state' => Order::STATE_PROCESSING,
                'expected_state' => Order::STATE_PROCESSING,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'complete - !canCreditmemo,canShip -> complete' => [
                'can_credit_memo' => false,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_COMPLETE,
                'expected_state' => Order::STATE_COMPLETE,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'processing - canCreditmemo,!canShip -> complete' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_PROCESSING,
                'expected_state' => Order::STATE_COMPLETE,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'complete - canCreditmemo,!canShip -> complete' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_COMPLETE,
                'expected_state' => Order::STATE_COMPLETE,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'processing - canCreditmemo, canShip -> processing' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_PROCESSING,
                'expected_state' => Order::STATE_PROCESSING,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'complete - canCreditmemo, canShip -> complete' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_COMPLETE,
                'expected_state' => Order::STATE_COMPLETE,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'new - canCreditmemo, canShip, IsInProcess -> processing' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_NEW,
                'expected_state' => Order::STATE_PROCESSING,
                'is_in_process' => true,
                'get_is_in_process_invoke_count' => 1,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'new - canCreditmemo, !canShip, IsInProcess -> processing' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_NEW,
                'expected_state' => Order::STATE_COMPLETE,
                'is_in_process' => true,
                'get_is_in_process_invoke_count' => 1,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'new - canCreditmemo, canShip, !IsInProcess -> new' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_NEW,
                'expected_state' => Order::STATE_NEW,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 1,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'hold - canUnhold -> hold' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_HOLDED,
                'expected_state' => Order::STATE_HOLDED,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => true,
                'is_not_virtual' => true
            ],
            'payment_review - canUnhold -> payment_review' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_PAYMENT_REVIEW,
                'expected_state' => Order::STATE_PAYMENT_REVIEW,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => true,
                'is_not_virtual' => true
            ],
            'pending_payment - canUnhold -> pending_payment' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_PENDING_PAYMENT,
                'expected_state' => Order::STATE_PENDING_PAYMENT,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => true,
                'is_not_virtual' => true
            ],
            'cancelled - isCanceled -> cancelled' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_HOLDED,
                'expected_state' => Order::STATE_HOLDED,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => true,
                'can_unhold' => false,
                'is_not_virtual' => true
            ],
            'processing - !canCreditmemo!canShip -> complete(virtual product)' => [
                'can_credit_memo' => false,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 2,
                'current_state' => Order::STATE_PROCESSING,
                'expected_state' => Order::STATE_COMPLETE,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => false
            ],
            'complete - !canCreditmemo, !canShip - closed(virtual product)' => [
                'can_credit_memo' => false,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_COMPLETE,
                'expected_state' => Order::STATE_CLOSED,
                'is_in_process' => false,
                'get_is_in_process_invoke_count' => 0,
                'is_canceled' => false,
                'can_unhold' => false,
                'is_not_virtual' => false,
            ],
        ];
    }
}
