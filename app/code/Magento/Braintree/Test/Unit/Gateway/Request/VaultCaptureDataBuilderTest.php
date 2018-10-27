<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Gateway\Request;

<<<<<<< HEAD
use Magento\Braintree\Gateway\SubjectReader;
=======
>>>>>>> upstream/2.2-develop
use Magento\Braintree\Gateway\Request\VaultCaptureDataBuilder;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\PaymentToken;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
<<<<<<< HEAD
 * Tests \Magento\Braintree\Gateway\Request\VaultCaptureDataBuilder.
=======
 * Tests VaultCaptureDataBuilder.
>>>>>>> upstream/2.2-develop
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
     * @var SubjectReader|MockObject
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
<<<<<<< HEAD
        $this->paymentDOMock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);
=======
        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);
>>>>>>> upstream/2.2-develop

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new VaultCaptureDataBuilder($this->subjectReaderMock);
    }

    /**
     * Checks the result after builder execution.
     */
    public function testBuild()
    {
        $amount = 30.00;
        $token = '5tfm4c';
        $buildSubject = [
<<<<<<< HEAD
            'payment' => $this->paymentDOMock,
=======
            'payment' => $this->paymentDO,
>>>>>>> upstream/2.2-develop
            'amount' => $amount,
        ];

        $expected = [
            'amount' => $amount,
            'paymentMethodToken' => $token,
        ];

<<<<<<< HEAD
        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        $paymentExtensionMock = $this->getMockBuilder(OrderPaymentExtension::class)
=======
        $this->subjectReader->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDO);
        $this->subjectReader->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        /** @var OrderPaymentExtension|MockObject $paymentExtension */
        $paymentExtension = $this->getMockBuilder(OrderPaymentExtension::class)
>>>>>>> upstream/2.2-develop
            ->setMethods(['getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

<<<<<<< HEAD
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
=======
        /** @var PaymentToken|MockObject $paymentToken */
        $paymentToken = $this->getMockBuilder(PaymentToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentExtension->method('getVaultPaymentToken')
            ->willReturn($paymentToken);
        $this->payment->method('getExtensionAttributes')
            ->willReturn($paymentExtension);

        $paymentToken->method('getGatewayToken')
>>>>>>> upstream/2.2-develop
            ->willReturn($token);

        $result = $this->builder->build($buildSubject);
        self::assertEquals($expected, $result);
<<<<<<< HEAD
=======
    }

    /**
     * Checks a builder execution if Payment Token doesn't exist.
     *
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     * @expectedExceptionMessage The Payment Token is not available to perform the request.
     */
    public function testBuildWithoutPaymentToken(): void
    {
        $amount = 30.00;
        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => $amount,
        ];

        $this->subjectReader->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDO);
        $this->subjectReader->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        /** @var OrderPaymentExtension|MockObject $paymentExtension */
        $paymentExtension = $this->getMockBuilder(OrderPaymentExtension::class)
            ->setMethods(['getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->payment->method('getExtensionAttributes')
            ->willReturn($paymentExtension);
        $paymentExtension->method('getVaultPaymentToken')
            ->willReturn(null);

        $this->builder->build($buildSubject);
>>>>>>> upstream/2.2-develop
    }
}
