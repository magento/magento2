<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Request\VaultCaptureDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\PaymentToken;

class VaultCaptureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VaultCaptureDataBuilder
     */
    private $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDO;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReader;

    public function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new VaultCaptureDataBuilder($this->subjectReader);
    }

    /**
     * \Magento\Braintree\Gateway\Request\VaultCaptureDataBuilder::build
     */
    public function testBuild()
    {
        $amount = 30.00;
        $token = '5tfm4c';
        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => $amount
        ];

        $expected = [
            'amount' => $amount,
            'paymentMethodToken' => $token
        ];

        $this->subjectReader->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDO);
        $this->subjectReader->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        $paymentExtension = $this->getMockBuilder(OrderPaymentExtension::class)
            ->setMethods(['getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $paymentToken = $this->getMockBuilder(PaymentToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentExtension->expects(static::once())
            ->method('getVaultPaymentToken')
            ->willReturn($paymentToken);
        $this->payment->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($paymentExtension);

        $paymentToken->expects(static::once())
            ->method('getGatewayToken')
            ->willReturn($token);

        $result = $this->builder->build($buildSubject);
        static::assertEquals($expected, $result);
    }
}
