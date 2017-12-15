<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\CaptureDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Braintree\Gateway\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class CaptureDataBuilderTest
 */
class CaptureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Braintree\Gateway\Request\CaptureDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $paymentDO;

    protected function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new CaptureDataBuilder(new SubjectReader());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage No authorization transaction to proceed capture.
     */
    public function testBuildWithException()
    {
        $amount = 10.00;
        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => $amount
        ];

        $this->payment->method('getCcTransId')
            ->willReturn('');

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->builder->build($buildSubject);
    }

    public function testBuild()
    {
        $transactionId = 'b3b99d';
        $amount = 10.00;

        $expected = [
            'transaction_id' => $transactionId,
            'amount' => $amount
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => $amount
        ];

        $this->payment->method('getCcTransId')
            ->willReturn($transactionId);

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        self::assertEquals($expected, $this->builder->build($buildSubject));
    }
}
