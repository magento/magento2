<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Magento\BraintreeTwo\Gateway\Response\CloneDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CloneDetailsHandlerTest
 */
class CloneDetailsHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TRANSACTION_ID = '432erw9we';

    /**
     * @var \Magento\BraintreeTwo\Gateway\Response\CloneDetailsHandler
     */
    private $cloneHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $payment;

    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setIsTransactionClosed', 'setTransactionId'
            ])
            ->getMock();

        $this->cloneHandler = new CloneDetailsHandler();
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Response\CloneDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->payment->expects(static::once())
            ->method('setIsTransactionClosed')
            ->with(false);
        $this->payment->expects(static::once())
            ->method('setTransactionId')
            ->with(self::TRANSACTION_ID);

        $response = [
            'object' => $this->getBraintreeTransaction()
        ];

        $this->cloneHandler->handle($subject, $response);
    }

    /**
     * Create mock for payment data object and order payment
     * @return MockObject
     */
    private function getPaymentDataObjectMock()
    {
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
     * Create Braintree transaction
     * @return MockObject
     */
    private function getBraintreeTransaction()
    {
        $attributes = [
            'id' => self::TRANSACTION_ID,
        ];

        $transaction = Transaction::factory($attributes);

        $mock = $this->getMockBuilder(Successful::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $mock->expects(static::once())
            ->method('__get')
            ->with('transaction')
            ->willReturn($transaction);

        return $mock;
    }
}
