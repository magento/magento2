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
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
    private $config;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
<<<<<<< HEAD
     * @var MockObject
=======
     * @var PaymentDataObjectInterface|MockObject
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private $paymentDOMock;

    protected function setUp()
    {
<<<<<<< HEAD
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->config = $this->getMockBuilder(Config::class)
=======
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMock = $this->getMockBuilder(Payment::class)
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            ->disableOriginalConstructor()
            ->getMock();
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new KountPaymentDataBuilder($this->config, new SubjectReader());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [];

<<<<<<< HEAD
        $this->config->method('hasFraudProtection')
=======
        $this->configMock->expects(self::never())
            ->method('hasFraudProtection')
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            ->willReturn(true);

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

        $order = $this->createMock(OrderAdapterInterface::class);
<<<<<<< HEAD
        $this->paymentDO->method('getOrder')
            ->willReturn($order);

        $buildSubject = ['payment' => $this->paymentDO];

        $this->payment->expects(self::exactly(count($additionalData)))
            ->method('getAdditionalInformation')
            ->willReturn($additionalData);

        $this->config->method('hasFraudProtection')
            ->willReturn(true);

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);
=======
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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

        self::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
