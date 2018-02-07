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
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class VaultCaptureDataBuilderTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->paymentDO = $this->getMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->builder = new VaultCaptureDataBuilder(new SubjectReader());
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

        $paymentExtension = $this->getMockBuilder(OrderPaymentExtension::class)
            ->setMethods(['getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
}
