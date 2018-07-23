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
 * Class PaymentDataBuilderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    const PAYMENT_METHOD_NONCE = 'nonce';
    const MERCHANT_ACCOUNT_ID = '245345';

    /**
     * @var PaymentDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var MockObject
     */
    private $paymentDO;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $order;

    protected function setUp()
    {
        $this->paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order = $this->createMock(OrderAdapterInterface::class);

        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new PaymentDataBuilder($config, new SubjectReader());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [];

        $this->builder->build($buildSubject);
    }

    /**
     * @expectedException \InvalidArgumentException
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
        $additionalData = [
            [
                DataAssignObserver::PAYMENT_METHOD_NONCE,
                self::PAYMENT_METHOD_NONCE
            ]
        ];

        $expectedResult = [
            PaymentDataBuilder::AMOUNT  => 10.00,
            PaymentDataBuilder::PAYMENT_METHOD_NONCE  => self::PAYMENT_METHOD_NONCE,
            PaymentDataBuilder::ORDER_ID => '000000101'
        ];

        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => 10.00
        ];

        $this->payment->expects(self::exactly(count($additionalData)))
            ->method('getAdditionalInformation')
            ->willReturnMap($additionalData);

        $this->paymentDO->method('getPayment')
            ->willReturn($this->payment);

        $this->paymentDO->method('getOrder')
            ->willReturn($this->order);

        $this->order->method('getOrderIncrementId')
            ->willReturn('000000101');

        self::assertEquals(
            $expectedResult,
            $this->builder->build($buildSubject)
        );
    }
}
