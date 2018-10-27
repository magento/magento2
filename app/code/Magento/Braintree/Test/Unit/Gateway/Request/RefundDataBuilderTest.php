<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

<<<<<<< HEAD
=======
use Magento\Braintree\Gateway\SubjectReader;
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
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
=======
>>>>>>> upstream/2.2-develop
     * @var RefundDataBuilder
     */
    private $dataBuilder;

    /**
     * @var string
     */
    private $transactionId = 'xsd7n';

    public function setUp()
    {
<<<<<<< HEAD
        $this->paymentModelMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataBuilder = new RefundDataBuilder($this->subjectReaderMock);
=======
        $this->dataBuilder = new RefundDataBuilder(new SubjectReader());
>>>>>>> upstream/2.2-develop
    }

    public function testBuild()
    {
<<<<<<< HEAD
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
=======
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $buildSubject = ['payment' => $paymentDO, 'amount' => 12.358];
        $transactionId = 'xsd7n';

        $paymentDO->method('getPayment')
            ->willReturn($paymentModel);
        $paymentModel->method('getParentTransactionId')
            ->willReturn($transactionId);
>>>>>>> upstream/2.2-develop

        self::assertEquals(
            [
                'transaction_id' => $this->transactionId,
                PaymentDataBuilder::AMOUNT => '12.36',
            ],
            $this->dataBuilder->build($buildSubject)
        );
    }

    public function testBuildNullAmount()
    {
<<<<<<< HEAD
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
=======
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $buildSubject = ['payment' => $paymentDO];
        $transactionId = 'xsd7n';

        $paymentDO->method('getPayment')
            ->willReturn($paymentModel);
        $paymentModel->method('getParentTransactionId')
            ->willReturn($transactionId);
>>>>>>> upstream/2.2-develop

        self::assertEquals(
            [
                'transaction_id' => $this->transactionId,
                PaymentDataBuilder::AMOUNT => null,
            ],
            $this->dataBuilder->build($buildSubject)
        );
    }

    public function testBuildCutOffLegacyTransactionIdPostfix()
    {
<<<<<<< HEAD
        $this->initPaymentDOMock();
        $buildSubject = ['payment' => $this->paymentDOMock];
=======
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $buildSubject = ['payment' => $paymentDO];
>>>>>>> upstream/2.2-develop
        $legacyTxnId = 'xsd7n-' . TransactionInterface::TYPE_CAPTURE;

<<<<<<< HEAD
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
=======
        $paymentDO->method('getPayment')
            ->willReturn($paymentModel);
        $paymentModel->method('getParentTransactionId')
            ->willReturn($legacyTxnId);
>>>>>>> upstream/2.2-develop

        self::assertEquals(
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
