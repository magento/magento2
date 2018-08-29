<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\VaultCaptureDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\PaymentToken;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Request\VaultCaptureDataBuilder.
 */
class VaultCaptureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VaultCaptureDataBuilder
     */
    private $builder;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDOMock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new VaultCaptureDataBuilder($this->subjectReaderMock);
    }

    /**
     * \Magento\Braintree\Gateway\Request\VaultCaptureDataBuilder::build
     */
    public function testBuild()
    {
        $amount = 30.00;
        $token = '5tfm4c';
        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => $amount,
        ];

        $expected = [
            'amount' => $amount,
            'paymentMethodToken' => $token,
        ];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        $paymentExtensionMock = $this->getMockBuilder(OrderPaymentExtension::class)
            ->setMethods(['getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $paymentTokenMock = $this->getMockBuilder(PaymentToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentExtensionMock->expects(static::once())
            ->method('getVaultPaymentToken')
            ->willReturn($paymentTokenMock);
        $this->paymentMock->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($paymentExtensionMock);

        $paymentTokenMock->expects(static::once())
            ->method('getGatewayToken')
            ->willReturn($token);

        $result = $this->builder->build($buildSubject);
        self::assertEquals($expected, $result);
    }
}
