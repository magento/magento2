<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Magento\Braintree\Gateway\Response\PaymentDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use Magento\Braintree\Gateway\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \\Magento\Braintree\Gateway\Response\PaymentDetailsHandler.
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
    private $paymentMock;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    protected function setUp()
    {
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setCcTransId',
                'setLastTransId',
                'setAdditionalInformation',
            ])
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMock->expects(static::once())
            ->method('setCcTransId');
        $this->paymentMock->expects(static::once())
            ->method('setLastTransId');
        $this->paymentMock->expects(static::any())
            ->method('setAdditionalInformation');

        $this->paymentHandler = new PaymentDetailsHandler($this->subjectReaderMock);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\PaymentDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentDataMock = $this->getPaymentDataObjectMock();
        $transaction = $this->getBraintreeTransaction();

        $subject = ['payment' => $paymentDataMock];
        $response = ['object' => $transaction];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentDataMock);
        $this->subjectReaderMock->expects(self::once())
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
            ->willReturn($this->paymentMock);

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
            'processorResponseText' => 'Approved',
        ];

        return Transaction::factory($attributes);
    }
}
