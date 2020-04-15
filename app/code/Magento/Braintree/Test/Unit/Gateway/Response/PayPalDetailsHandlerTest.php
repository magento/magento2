<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Magento\Braintree\Gateway\Response\PayPalDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use Magento\Braintree\Gateway\SubjectReader;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Class PayPalDetailsHandlerTest
 */
class PayPalDetailsHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PayPalDetailsHandler|MockObject
     */
    private $payPalHandler;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    protected function setUp(): void
    {
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setAdditionalInformation',
            ])
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payPalHandler = new PayPalDetailsHandler($this->subjectReaderMock);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\PayPalDetailsHandler::handle
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
        $this->subjectReaderMock->expects(static::once())
            ->method('readPayPal')
            ->with($transaction)
            ->willReturn($transaction->paypal);

        $this->paymentMock->expects(static::exactly(2))
            ->method('setAdditionalInformation');

        $this->payPalHandler->handle($subject, $response);
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
            'id' => '23ui8be',
            'paypal' => [
                'paymentId' => 'u239dkv6n2lds',
                'payerEmail' => 'example@test.com'
            ]
        ];

        $transaction = Transaction::factory($attributes);

        return $transaction;
    }
}
