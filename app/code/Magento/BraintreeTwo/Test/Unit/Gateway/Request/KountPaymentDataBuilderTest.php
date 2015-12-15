<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Request;

use Magento\Sales\Model\Order\Payment;
use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\BraintreeTwo\Gateway\Request\KountPaymentDataBuilder;

/**
 * Class KountPaymentDataBuilderTest
 *
 * @see \Magento\BraintreeTwo\Gateway\Request\KountPaymentDataBuilder
 */
class KountPaymentDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    const DEVICE_DATA = '{"test": "test"}';

    /**
     * @var KountPaymentDataBuilder
     */
    private $builder;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDO;

    public function setUp()
    {
        $this->paymentDO = $this->getMock(PaymentDataObjectInterface::class);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new KountPaymentDataBuilder($this->configMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Payment data object should be provided
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [];

        $this->configMock->expects(static::once())
            ->method('getIsFraudProtection')
            ->willReturn(true);

        $this->builder->build($buildSubject);
    }

    public function testBuild()
    {
        $additionalData = [
            [
                DataAssignObserver::DEVICE_DATA,
                self::DEVICE_DATA
            ]
        ];

        $expectedResult = [
            KountPaymentDataBuilder::DEVICE_DATA => self::DEVICE_DATA,
        ];

        $buildSubject = ['payment' => $this->paymentDO];

        $this->paymentMock->expects(static::exactly(count($additionalData)))
            ->method('getAdditionalInformation')
            ->willReturnMap($additionalData);

        $this->configMock->expects(static::once())
            ->method('getIsFraudProtection')
            ->willReturn(true);

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        static::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
