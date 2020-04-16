<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Response\CardDetailsHandler;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\Braintree\Gateway\Response\CardDetailsHandler.
 */
class CardDetailsHandlerTest extends TestCase
{
    /**
     * @var CardDetailsHandler
     */
    private $cardHandler;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    protected function setUp(): void
    {
        $this->initConfigMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cardHandler = new CardDetailsHandler($this->configMock, $this->subjectReaderMock);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\CardDetailsHandler::handle
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

        $this->paymentMock->expects(static::once())
            ->method('setCcLast4');
        $this->paymentMock->expects(static::once())
            ->method('setCcExpMonth');
        $this->paymentMock->expects(static::once())
            ->method('setCcExpYear');
        $this->paymentMock->expects(static::once())
            ->method('setCcType');
        $this->paymentMock->expects(static::exactly(2))
            ->method('setAdditionalInformation');

        $this->cardHandler->handle($subject, $response);
    }

    /**
     * Create mock for gateway config
     */
    private function initConfigMock()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCctypesMapper'])
            ->getMock();

        $this->configMock->expects(static::once())
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
     * @return MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $this->paymentMock = $this->getMockBuilder(Payment::class)
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
