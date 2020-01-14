<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Gateway\Request\RefundDataBuilder;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Request\RefundDataBuilder.
 */
class RefundDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentModelMock;

    /**
     * @var RefundDataBuilder
     */
    private $dataBuilder;

    /**
     * @var string
     */
    private $transactionId = 'xsd7n';

    public function setUp()
    {
        $this->paymentModelMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataBuilder = new RefundDataBuilder($this->subjectReaderMock);
    }

    public function testBuild()
    {
        $this->initPaymentDOMock();
        $buildSubject = ['payment' => $this->paymentDOMock, 'amount' => 12.358];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->paymentModelMock->expects(self::once())
            ->method('getParentTransactionId')
            ->willReturn($this->transactionId);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn($buildSubject['amount']);

        static::assertEquals(
            [
                'transaction_id' => $this->transactionId,
                PaymentDataBuilder::AMOUNT => '12.36',
            ],
            $this->dataBuilder->build($buildSubject)
        );
    }

    public function testBuildNullAmount()
    {
        $this->initPaymentDOMock();
        $buildSubject = ['payment' => $this->paymentDOMock];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->paymentModelMock->expects(self::once())
            ->method('getParentTransactionId')
            ->willReturn($this->transactionId);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willThrowException(new \InvalidArgumentException());

        static::assertEquals(
            [
                'transaction_id' => $this->transactionId,
                PaymentDataBuilder::AMOUNT => null,
            ],
            $this->dataBuilder->build($buildSubject)
        );
    }

    public function testBuildCutOffLegacyTransactionIdPostfix()
    {
        $this->initPaymentDOMock();
        $buildSubject = ['payment' => $this->paymentDOMock];
        $legacyTxnId = 'xsd7n-' . TransactionInterface::TYPE_CAPTURE;

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->paymentModelMock->expects(self::once())
            ->method('getParentTransactionId')
            ->willReturn($legacyTxnId);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willThrowException(new \InvalidArgumentException());

        static::assertEquals(
            [
                'transaction_id' => $this->transactionId,
                PaymentDataBuilder::AMOUNT => null,
            ],
            $this->dataBuilder->build($buildSubject)
        );
    }

    /**
     * Creates mock object for PaymentDataObjectInterface
     *
     * @return PaymentDataObjectInterface|MockObject
     */
    private function initPaymentDOMock()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentDOMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($this->paymentModelMock);
    }
}
