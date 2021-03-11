<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\VaultCaptureDataBuilder;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\PaymentToken;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

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
    private $paymentDO;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new VaultCaptureDataBuilder($this->subjectReader);
    }

    /**
     * Checks the result after builder execution.
     */
    public function testBuild(): void
    {
        $amount = 30.00;
        $token = '5tfm4c';
        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => $amount,
        ];

        $expected = [
            'amount' => $amount,
            'paymentMethodToken' => $token,
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

        /** @var PaymentToken|MockObject $paymentToken */
        $paymentToken = $this->getMockBuilder(PaymentToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentExtension->method('getVaultPaymentToken')
            ->willReturn($paymentToken);
        $this->payment->method('getExtensionAttributes')
            ->willReturn($paymentExtension);

        $paymentToken->method('getGatewayToken')
            ->willReturn($token);

        $result = $this->builder->build($buildSubject);
        self::assertEquals($expected, $result);
    }

    /**
     * Checks a builder execution if Payment Token doesn't exist.
     *
     */
    public function testBuildWithoutPaymentToken(): void
    {
        $this->expectException(\Magento\Payment\Gateway\Command\CommandException::class);
        $this->expectExceptionMessage('The Payment Token is not available to perform the request.');

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
    }
}
