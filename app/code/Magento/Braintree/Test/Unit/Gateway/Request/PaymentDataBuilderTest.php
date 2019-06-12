<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Braintree\Gateway\Config\Config;

/**
 * Tests \Magento\Braintree\Gateway\Request\PaymentDataBuilder.
 */
class PaymentDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    const PAYMENT_METHOD_NONCE = 'nonce';

    /**
     * @var PaymentDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
<<<<<<< HEAD
     * @var MockObject
=======
     * @var PaymentDataObjectInterface|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $paymentDOMock;

    /**
<<<<<<< HEAD
=======
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @var OrderAdapterInterface|MockObject
     */
    private $order;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
<<<<<<< HEAD
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order = $this->createMock(OrderAdapterInterface::class);

        $config = $this->getMockBuilder(Config::class)
=======
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->disableOriginalConstructor()
            ->getMock();

<<<<<<< HEAD
        $this->builder = new PaymentDataBuilder($config, new SubjectReader());
=======
        /** @var Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new PaymentDataBuilder($config, $this->subjectReaderMock);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @return void
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException(): void
    {
        $buildSubject = [];

        $this->builder->build($buildSubject);
    }

    /**
     * @return void
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadAmountException(): void
    {
        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => null,
        ];

<<<<<<< HEAD
=======
        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willThrowException(new \InvalidArgumentException());

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->builder->build($buildSubject);
    }

    /**
     * @return void
     */
    public function testBuild(): void
    {
        $additionalData = [
            [
                DataAssignObserver::PAYMENT_METHOD_NONCE,
                self::PAYMENT_METHOD_NONCE,
            ],
        ];

        $expectedResult = [
            PaymentDataBuilder::AMOUNT  => 10.00,
            PaymentDataBuilder::PAYMENT_METHOD_NONCE  => self::PAYMENT_METHOD_NONCE,
            PaymentDataBuilder::ORDER_ID => '000000101'
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => 10.00,
        ];

<<<<<<< HEAD
        $this->payment->expects(self::exactly(count($additionalData)))
            ->method('getAdditionalInformation')
            ->willReturnMap($additionalData);

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

        $this->order->method('getOrderIncrementId')
=======
        $this->paymentMock->expects(self::exactly(count($additionalData)))
            ->method('getAdditionalInformation')
            ->willReturnMap($additionalData);

        $this->paymentDOMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentDOMock->expects(self::once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(self::once())
            ->method('readAmount')
            ->with($buildSubject)
            ->willReturn(10.00);

        $this->orderMock->expects(self::once())
            ->method('getOrderIncrementId')
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->willReturn('000000101');

        self::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
