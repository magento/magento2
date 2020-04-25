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
                    'getConfig'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock->expects($this->any())
            ->method('getConfig')
            ->willReturnSelf();
        $this->addressMock = $this->createMock(Address::class);
        $this->addressCollectionMock = $this->createMock(
            Collection::class
        );
        $this->state = new State();
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

    /**
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
                'expected_state' => Order::STATE_CLOSED
            ],
            'complete - !canCreditmemo,!canShip -> closed' => [
                'can_credit_memo' => false,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_COMPLETE,
                'expected_state' => Order::STATE_CLOSED
            ],
            'processing - !canCreditmemo,canShip -> processing' => [
                'can_credit_memo' => false,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 2,
                'current_state' => Order::STATE_PROCESSING,
                'expected_state' => Order::STATE_PROCESSING
            ],
            'complete - !canCreditmemo,canShip -> complete' => [
                'can_credit_memo' => false,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_COMPLETE,
                'expected_state' => Order::STATE_COMPLETE
            ],
            'processing - canCreditmemo,!canShip -> complete' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_PROCESSING,
                'expected_state' => Order::STATE_COMPLETE
            ],
            'complete - canCreditmemo,!canShip -> complete' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_COMPLETE,
                'expected_state' => Order::STATE_COMPLETE
            ],
            'processing - canCreditmemo, canShip -> processing' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_PROCESSING,
                'expected_state' => Order::STATE_PROCESSING
            ],
            'complete - canCreditmemo, canShip -> complete' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_COMPLETE,
                'expected_state' => Order::STATE_COMPLETE
            ],
            'new - canCreditmemo, canShip, IsInProcess -> processing' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => true,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_NEW,
                'expected_state' => Order::STATE_PROCESSING,
                true,
                1
            ],
            'new - canCreditmemo, !canShip, IsInProcess -> processing' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 1,
                'can_ship' => false,
                'call_can_skip_num' => 1,
                'current_state' => Order::STATE_NEW,
                'expected_state' => Order::STATE_COMPLETE,
                true,
                1
            ],
            'new - canCreditmemo, canShip, !IsInProcess -> new' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_NEW,
                'expected_state' => Order::STATE_NEW,
                false,
                1
            ],
            'hold - canUnhold -> hold' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_HOLDED,
                'expected_state' => Order::STATE_HOLDED,
                false,
                0,
                false,
                true
            ],
            'payment_review - canUnhold -> payment_review' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_PAYMENT_REVIEW,
                'expected_state' => Order::STATE_PAYMENT_REVIEW,
                false,
                0,
                false,
                true
            ],
            'pending_payment - canUnhold -> pending_payment' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_PENDING_PAYMENT,
                'expected_state' => Order::STATE_PENDING_PAYMENT,
                false,
                0,
                false,
                true
            ],
            'cancelled - isCanceled -> cancelled' => [
                'can_credit_memo' => true,
                'can_credit_memo_invoke_count' => 0,
                'can_ship' => true,
                'call_can_skip_num' => 0,
                'current_state' => Order::STATE_HOLDED,
                'expected_state' => Order::STATE_HOLDED,
                false,
                0,
                true
            ],
        ];
    }
}
