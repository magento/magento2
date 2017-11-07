<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Magento\Braintree\Gateway\Response\CardDetailsHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\SubjectReader;

/**
 * Class CardDetailsHandlerTest
 */
class CardDetailsHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Braintree\Gateway\Response\CardDetailsHandler
     */
    private $cardHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var \Magento\Braintree\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    protected function setUp()
    {
        $this->initConfigMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cardHandler = new CardDetailsHandler($this->config, $this->subjectReaderMock);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\CardDetailsHandler::handle
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $transaction = $this->getBraintreeTransaction();

        $subject = ['payment' => $paymentData];
        $response = ['object' => $transaction];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);
        $this->subjectReaderMock->expects(self::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);

        $this->payment->expects(static::once())
            ->method('setCcLast4');
        $this->payment->expects(static::once())
            ->method('setCcExpMonth');
        $this->payment->expects(static::once())
            ->method('setCcExpYear');
        $this->payment->expects(static::once())
            ->method('setCcType');
        $this->payment->expects(static::exactly(2))
            ->method('setAdditionalInformation');

        $this->cardHandler->handle($subject, $response);
    }

    /**
     * Create mock for gateway config
     */
    private function initConfigMock()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCctypesMapper'])
            ->getMock();

        $this->config->expects(static::once())
            ->method('getCctypesMapper')
            ->willReturn([
                'american-express' => 'AE',
                'discover' => 'DI',
                'jcb' => 'JCB',
                'mastercard' => 'MC',
                'master-card' => 'MC',
                'visa' => 'VI'
            ]);
    }

    /**
     * Create mock for payment data object and order payment
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setCcLast4',
                'setCcExpMonth',
                'setCcExpYear',
                'setCcType',
                'setAdditionalInformation',
            ])
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
     * Create Braintree transaction
     * @return \Braintree\Transaction
     */
    private function getBraintreeTransaction()
    {
        $attributes = [
            'creditCard' => [
                'bin' => '5421',
                'cardType' => 'American Express',
                'expirationMonth' => 12,
                'expirationYear' => 21,
                'last4' => 1231
            ]
        ];
        $transaction = Transaction::factory($attributes);

        return $transaction;
    }
}
