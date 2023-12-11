<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Payment\State;

use Magento\Directory\Model\Currency;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\AuthorizeCommand;
use Magento\Sales\Model\Order\StatusResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see AuthorizeCommand
 */
class AuthorizeCommandTest extends TestCase
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
     * @see AuthorizeCommand::execute
     *
     * @param bool $isTransactionPending
     * @param bool $isFraudDetected
     * @param string $expectedState
     * @param string $expectedStatus
     * @param string $expectedMessage
     *
     * @dataProvider commandResultDataProvider
     */
    public function testExecute(
        $isTransactionPending,
        $isFraudDetected,
        $expectedState,
        $expectedStatus,
        $expectedMessage
    ) {
        $actualReturn = (new AuthorizeCommand($this->getStatusResolver()))->execute(
            $this->getPayment($isTransactionPending, $isFraudDetected),
            $this->amount,
            $this->getOrder()
        );

        $this->assertOrderStateAndStatus($this->getOrder(), $expectedState, $expectedStatus);
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
                Order::STATE_PROCESSING,
                $this->newOrderStatus,
                'Authorized amount of %1.'
            ],
            [
                true,
                false,
                Order::STATE_PAYMENT_REVIEW,
                $this->newOrderStatus,
                'We will authorize %1 after the payment is approved at the payment gateway.'
            ],
            [
                false,
                true,
                Order::STATE_PAYMENT_REVIEW,
                Order::STATUS_FRAUD,
                'Authorized amount of %1.' .
                ' Order is suspended as its authorizing amount %1 is suspected to be fraudulent.'
            ],
            [
                true,
                true,
                Order::STATE_PAYMENT_REVIEW,
                Order::STATUS_FRAUD,
                'We will authorize %1 after the payment is approved at the payment gateway.' .
                ' Order is suspended as its authorizing amount %1 is suspected to be fraudulent.'
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
     * @return Order|MockObject
     */
    private function getOrder()
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->method('getBaseCurrency')
            ->willReturn($this->getCurrency());

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
        $order->method('setState')->with($expectedState);
        $order->method('setStatus')->with($expectedStatus);
    }
}
