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
 * Tests \Magento\Braintree\Gateway\Request\CaptureDataBuilder.
 */
class CaptureDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaptureDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
<<<<<<< HEAD
     * @var \Magento\Sales\Model\Order\Payment|MockObject
=======
     * @var Payment|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $paymentDOMock;

<<<<<<< HEAD
=======
    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
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
            'payment' => $this->paymentDOMock,
            'amount' => $amount,
        ];

<<<<<<< HEAD
        $this->payment->method('getCcTransId')
            ->willReturn('');

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

=======
        $this->paymentMock->expects(self::once())
            ->method('getCcTransId')
            ->willReturn('');

        $this->paymentDOMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->builder->build($buildSubject);
    }

    public function testBuild()
    {
        $transactionId = 'b3b99d';
        $amount = 10.00;

        $expected = [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => $amount,
        ];

<<<<<<< HEAD
        $this->payment->method('getCcTransId')
            ->willReturn($transactionId);

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        self::assertEquals($expected, $this->builder->build($buildSubject));
=======
        $this->paymentMock->expects(self::once())
            ->method('getCcTransId')
            ->willReturn($transactionId);

        $this->paymentDOMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn($amount);

        static::assertEquals($expected, $this->builder->build($buildSubject));
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }
}
