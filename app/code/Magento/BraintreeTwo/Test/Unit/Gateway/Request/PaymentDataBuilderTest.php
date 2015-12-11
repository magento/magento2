<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Request;

use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Gateway\Request\PaymentDataBuilder;
use Magento\BraintreeTwo\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class PaymentDataBuilderTest
 */
class PaymentDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    const PAYMENT_METHOD_NONCE = 'nonce';
    const MERCHANT_ACCOUNT_ID = '245345';

    /**
     * @var PaymentDataBuilder
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

        $this->builder = new PaymentDataBuilder($this->configMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Payment data object should be provided
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [];

        $this->builder->build($buildSubject);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Amount should be provided
     */
    public function testBuildReadAmountException()
    {
        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => null
        ];

        $this->builder->build($buildSubject);
    }

    public function testBuild()
    {
        $expectedResult = [
            PaymentDataBuilder::AMOUNT  => 10.00,
            PaymentDataBuilder::PAYMENT_METHOD_NONCE  => self::PAYMENT_METHOD_NONCE,
            PaymentDataBuilder::MERCHANT_ACCOUNT_ID  => self::MERCHANT_ACCOUNT_ID
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => 10.00
        ];

        $this->paymentMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->with(DataAssignObserver::PAYMENT_METHOD_NONCE)
            ->willReturn(self::PAYMENT_METHOD_NONCE);

        $this->configMock->expects(static::once())
            ->method('getValue')
            ->with(Config::KEY_MERCHANT_ACCOUNT_ID)
            ->willReturn(self::MERCHANT_ACCOUNT_ID);

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        static::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
