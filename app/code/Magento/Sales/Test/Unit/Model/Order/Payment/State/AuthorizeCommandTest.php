<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Payment\State;

use Magento\Directory\Model\Currency;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\State\AuthorizeCommand;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AuthorizeCommandTest
 * @see \Magento\Sales\Model\Order\Payment\State\AuthorizeCommand
 */
class AuthorizeCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var Currency|MockObject
     */
    private $currency;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var AuthorizeCommand
     */
    private $command;

    /**
     * @var int
     */
    private $amount = 45;

    protected function setUp()
    {
        $this->currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatTxt'])
            ->getMock();

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStateDefaultStatus'])
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsTransactionPending', 'getIsFraudDetected'])
            ->getMock();
        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrency', 'getConfig', 'setState', 'setStatus'])
            ->getMock();

        $this->order->expects(static::once())
            ->method('getBaseCurrency')
            ->willReturn($this->currency);
        $this->currency->expects(static::once())
            ->method('formatTxt')
            ->with($this->amount)
            ->willReturn($this->amount);

        $this->command = new AuthorizeCommand();
    }

    /**
     * @covers \Magento\Sales\Model\Order\Payment\State\AuthorizeCommand::execute
     */
    public function testExecute()
    {
        $message = __('Authorized amount of %1.', $this->amount);

        $this->payment->expects(static::once())
            ->method('getIsTransactionPending')
            ->willReturn(false);
        $this->payment->expects(static::once())
            ->method('getIsFraudDetected')
            ->willReturn(false);

        $this->order->expects(static::once())
            ->method('getConfig')
            ->willReturn($this->config);
        $this->config->expects(static::once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn(Order::STATE_PROCESSING);

        $this->order->expects(static::once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();
        $this->order->expects(static::once())
            ->method('setStatus')
            ->with(Order::STATE_PROCESSING);

        $actual = $this->command->execute($this->payment, $this->amount, $this->order);
        static::assertEquals($message, $actual);
    }

    /**
     * @covers \Magento\Sales\Model\Order\Payment\State\AuthorizeCommand::execute
     */
    public function testExecutePendingTransaction()
    {
        $message = __('We will authorize %1 after the payment is approved at the payment gateway.', $this->amount);

        $this->payment->expects(static::once())
            ->method('getIsTransactionPending')
            ->willReturn(true);
        $this->payment->expects(static::once())
            ->method('getIsFraudDetected')
            ->willReturn(false);

        $this->order->expects(static::once())
            ->method('getConfig')
            ->willReturn($this->config);
        $this->config->expects(static::once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_PAYMENT_REVIEW)
            ->willReturn(Order::STATE_PAYMENT_REVIEW);

        $this->order->expects(static::once())
            ->method('setState')
            ->with(Order::STATE_PAYMENT_REVIEW)
            ->willReturnSelf();
        $this->order->expects(static::once())
            ->method('setStatus')
            ->with(Order::STATE_PAYMENT_REVIEW);

        $actual = $this->command->execute($this->payment, $this->amount, $this->order);
        static::assertEquals($message, $actual);
    }

    /**
     * @covers \Magento\Sales\Model\Order\Payment\State\AuthorizeCommand::execute
     */
    public function testExecutePendingTransactionFraud()
    {
        $expectedMessage = 'We will authorize %1 after the payment is approved at the payment gateway. ';
        $expectedMessage .= 'Order is suspended as its authorizing amount %1 is suspected to be fraudulent.';
        $message = __($expectedMessage, $this->amount);

        $this->payment->expects(static::once())
            ->method('getIsTransactionPending')
            ->willReturn(true);
        $this->payment->expects(static::once())
            ->method('getIsFraudDetected')
            ->willReturn(true);

        $this->order->expects(static::never())
            ->method('getConfig');

        $this->order->expects(static::once())
            ->method('setState')
            ->with(Order::STATE_PAYMENT_REVIEW)
            ->willReturnSelf();
        $this->order->expects(static::once())
            ->method('setStatus')
            ->with(Order::STATUS_FRAUD);

        $actual = $this->command->execute($this->payment, $this->amount, $this->order);
        static::assertEquals($message, $actual);
    }

    /**
     * @covers \Magento\Sales\Model\Order\Payment\State\AuthorizeCommand::execute
     */
    public function testExecuteFraud()
    {
        $message = __(
            'Authorized amount of %1. Order is suspended as its authorizing amount %1 is suspected to be fraudulent.',
            $this->amount
        );

        $this->payment->expects(static::once())
            ->method('getIsTransactionPending')
            ->willReturn(false);
        $this->payment->expects(static::once())
            ->method('getIsFraudDetected')
            ->willReturn(true);

        $this->order->expects(static::never())
            ->method('getConfig');

        $this->order->expects(static::once())
            ->method('setState')
            ->with(Order::STATE_PAYMENT_REVIEW)
            ->willReturnSelf();
        $this->order->expects(static::once())
            ->method('setStatus')
            ->with(Order::STATUS_FRAUD);

        $actual = $this->command->execute($this->payment, $this->amount, $this->order);
        static::assertEquals($message, $actual);
    }
}
