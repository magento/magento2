<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Command;

use Magento\BraintreeTwo\Gateway\Command\CaptureStrategyCommand;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Class CaptureStrategyCommandTest
 */
class CaptureStrategyCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\BraintreeTwo\Gateway\Command\CaptureStrategyCommand
     */
    private $strategyCommand;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandPool;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory|
     * \PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionCollection;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var \Magento\Payment\Gateway\Command\GatewayCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $command;

    protected function setUp()
    {
        $this->commandPool = $this->getMockBuilder(CommandPoolInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', '__wakeup'])
            ->getMock();

        $this->initCommandMock();

        $factory = $this->getTransactionCollectionFactory();

        $this->strategyCommand = new CaptureStrategyCommand($this->commandPool, $factory);
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Command\CaptureStrategyCommand::execute
     */
    public function testSaleExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->payment->expects(static::once())
            ->method('getAuthorizationTransaction')
            ->willReturn(false);

        $this->payment->expects(static::never())
            ->method('getId');

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with(CaptureStrategyCommand::SALE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * @param int $size
     * @param string $command
     * @covers       \Magento\BraintreeTwo\Gateway\Command\CaptureStrategyCommand::execute
     * @dataProvider captureDataProvider
     */
    public function testCaptureExecute($size, $command)
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->payment->expects(static::once())
            ->method('getAuthorizationTransaction')
            ->willReturn(true);

        $this->payment->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $this->transactionCollection->expects(static::exactly(2))
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $this->transactionCollection->expects(static::once())
            ->method('getSize')
            ->willReturn($size);

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with($command)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * Return variations for command testing
     */
    public function captureDataProvider()
    {
        return [
            ['collectionSize' => 0, 'command' => CaptureStrategyCommand::CAPTURE],
            ['collectionSize' => 1, 'command' => CaptureStrategyCommand::CLONE_TRANSACTION]
        ];
    }

    /**
     * Create mock for payment data object and order payment
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthorizationTransaction', 'getId'])
            ->getMock();

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }

    /**
     * Create mock for gateway command object
     */
    private function initCommandMock()
    {
        $this->command = $this->getMockBuilder(GatewayCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->command->expects(static::once())
            ->method('execute')
            ->willReturn([]);
    }

    /**
     * Get mock for transaction collection factory
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getTransactionCollectionFactory()
    {
        $this->transactionCollection = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getSize', '__wakeup'])
            ->getMock();

        $mock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $mock->expects(static::any())
            ->method('create')
            ->willReturn($this->transactionCollection);

        return $mock;
    }
}
