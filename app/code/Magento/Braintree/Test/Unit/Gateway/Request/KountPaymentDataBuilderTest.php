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
 * Class KountPaymentDataBuilderTest
 *
 * @see \Magento\Braintree\Gateway\Request\KountPaymentDataBuilder
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
     * @var MockObject
     */
    private $paymentDO;

    protected function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->config = $this->getMockBuilder(Config::class)
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

        $this->config->method('hasFraudProtection')
            ->willReturn(true);

        $this->builder->build($buildSubject);
    }

    public function testBuild()
    {
        $additionalData = [
            DataAssignObserver::DEVICE_DATA => self::DEVICE_DATA
        ];

        $expectedResult = [
            KountPaymentDataBuilder::DEVICE_DATA => self::DEVICE_DATA,
        ];

        $order = $this->createMock(OrderAdapterInterface::class);
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

        self::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
