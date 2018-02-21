<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Magento\Braintree\Gateway\Response\PaymentDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Braintree\Gateway\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PaymentDetailsHandlerTest
 */
class PaymentDetailsHandlerTest extends \PHPUnit\Framework\TestCase
{
    const TRANSACTION_ID = '432erwwe';

    /**
     * @var \Magento\Braintree\Gateway\Response\PaymentDetailsHandler
     */
    private $paymentHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $payment;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setCcTransId',
                'setLastTransId',
                'setAdditionalInformation'
            ])
            ->getMock();
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->expects(static::once())
            ->method('setCcTransId');
        $this->payment->expects(static::once())
            ->method('setLastTransId');
        $this->payment->expects(static::any())
            ->method('setAdditionalInformation');

        $this->paymentHandler = new PaymentDetailsHandler($this->subjectReader);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\PaymentDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $transaction = $this->getBraintreeTransaction();

        $subject = ['payment' => $paymentData];
        $response = ['object' => $transaction];

        $this->subjectReader->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);
        $this->subjectReader->expects(self::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);

        $this->paymentHandler->handle($subject, $response);
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
     * @return Transaction
     */
    private function getBraintreeTransaction()
    {
        $attributes = [
            'id' => self::TRANSACTION_ID,
            'avsPostalCodeResponseCode' => 'M',
            'avsStreetAddressResponseCode' => 'M',
            'cvvResponseCode' => 'M',
            'processorAuthorizationCode' => 'W1V8XK',
            'processorResponseCode' => '1000',
            'processorResponseText' => 'Approved'
        ];

        return Transaction::factory($attributes);
    }
}
