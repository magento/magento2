<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Payment\State;

use Magento\Directory\Model\Currency;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\RegisterCaptureNotificationCommand;
use Magento\Sales\Model\Order\StatusResolver;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * @see RegisterCaptureNotificationCommand
 */
class RegisterCaptureNotificationCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var float
     */
    private $amount = 10.00;

    /**
     * @var string
     */
    private $newOrderStatus = 'custom_status';

    /**
     * @see RegisterCaptureNotificationCommand::execute
     *
     * @param bool $isTransactionPending
     * @param bool $isFraudDetected
     * @param string|null $currentState
     * @param string $expectedState
     * @param string $expectedStatus
     * @param string $expectedMessage
     * @return void
     * @dataProvider commandResultDataProvider
     */
    public function testExecute(
        bool $isTransactionPending,
        bool $isFraudDetected,
        $currentState,
        string $expectedState,
        string $expectedStatus,
        string $expectedMessage
    ): void {
        $order = $this->getOrder($currentState);
        $actualReturn = (new RegisterCaptureNotificationCommand($this->getStatusResolver()))->execute(
            $this->getPayment($isTransactionPending, $isFraudDetected),
            $this->amount,
            $order
        );

        $this->assertOrderStateAndStatus($order, $expectedState, $expectedStatus);
        self::assertEquals(__($expectedMessage, $this->amount), $actualReturn);
    }

    /**
     * @return array
     */
    public function commandResultDataProvider()
    {
        return [
            [
                false,
                false,
                Order::STATE_COMPLETE,
                Order::STATE_COMPLETE,
                $this->newOrderStatus,
                'Registered notification about captured amount of %1.',
            ],
            [
                false,
                false,
                null,
                Order::STATE_PROCESSING,
                $this->newOrderStatus,
                'Registered notification about captured amount of %1.',
            ],
            [
                true,
                false,
                Order::STATE_PROCESSING,
                Order::STATE_PAYMENT_REVIEW,
                $this->newOrderStatus,
                'An amount of %1 will be captured after being approved at the payment gateway.',
            ],
            [
                false,
                true,
                Order::STATE_PROCESSING,
                Order::STATE_PAYMENT_REVIEW,
                Order::STATUS_FRAUD,
                'Order is suspended as its capture amount %1 is suspected to be fraudulent.',
            ],
            [
                true,
                true,
                Order::STATE_PROCESSING,
                Order::STATE_PAYMENT_REVIEW,
                Order::STATUS_FRAUD,
                'Order is suspended as its capture amount %1 is suspected to be fraudulent.',
            ],
        ];
    }

    /**
     * @return StatusResolver|MockObject
     */
    private function getStatusResolver()
    {
        $statusResolver = $this->getMockBuilder(StatusResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statusResolver->method('getOrderStatusByState')
            ->willReturn($this->newOrderStatus);

        return $statusResolver;
    }

    /**
     * @param string|null $state
     * @return Order|MockObject
     */
    private function getOrder($state)
    {
        /** @var Order|MockObject $order */
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrency', 'getOrderStatusByState'])
            ->getMock();
        $order->method('getBaseCurrency')
            ->willReturn($this->getCurrency());
        $order->setState($state);

        return $order;
    }

    /**
     * @param bool $isTransactionPending
     * @param bool $isFraudDetected
     * @return OrderPaymentInterface|MockObject
     */
    private function getPayment($isTransactionPending, $isFraudDetected)
    {
        $payment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->setMethods(['getIsTransactionPending', 'getIsFraudDetected'])
            ->getMockForAbstractClass();
        $payment->method('getIsTransactionPending')
            ->willReturn($isTransactionPending);
        $payment->method('getIsFraudDetected')
            ->willReturn($isFraudDetected);

        return $payment;
    }

    /**
     * @return Currency|MockObject
     */
    private function getCurrency()
    {
        $currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currency->method('formatTxt')
            ->willReturn($this->amount);

        return $currency;
    }

    /**
     * @param Order|MockObject $order
     * @param string $expectedState
     * @param string $expectedStatus
     */
    private function assertOrderStateAndStatus($order, $expectedState, $expectedStatus)
    {
        self::assertEquals($expectedState, $order->getState(), 'The order {state} should match.');
        self::assertEquals($expectedStatus, $order->getStatus(), 'The order {status} should match.');
    }
}
