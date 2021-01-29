<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Braintree\Gateway\Request\KountPaymentDataBuilder;
use Magento\Braintree\Gateway\SubjectReader;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Request\KountPaymentDataBuilder.
 */
class KountPaymentDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    const DEVICE_DATA = '{"test": "test"}';

    /**
     * @var KountPaymentDataBuilder
     */
    private $builder;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @var SubjectReader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectReaderMock;

    protected function setUp(): void
    {
        $this->paymentDOMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new KountPaymentDataBuilder($this->configMock, $this->subjectReaderMock);
    }

    /**
     */
    public function testBuildReadPaymentException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $buildSubject = [];

        $this->configMock->expects(self::never())
            ->method('hasFraudProtection')
            ->willReturn(true);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willThrowException(new \InvalidArgumentException());

        $this->builder->build($buildSubject);
    }

    public function testBuild()
    {
        $additionalData = [
            DataAssignObserver::DEVICE_DATA => self::DEVICE_DATA,
        ];

        $expectedResult = [
            KountPaymentDataBuilder::DEVICE_DATA => self::DEVICE_DATA,
        ];

        $order = $this->getMockForAbstractClass(OrderAdapterInterface::class);
        $this->paymentDOMock->expects(self::once())->method('getOrder')->willReturn($order);

        $buildSubject = ['payment' => $this->paymentDOMock];

        $this->paymentMock->expects(self::exactly(count($additionalData)))
            ->method('getAdditionalInformation')
            ->willReturn($additionalData);

        $this->configMock->expects(self::once())
            ->method('hasFraudProtection')
            ->willReturn(true);

        $this->paymentDOMock->expects(self::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);

        static::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
