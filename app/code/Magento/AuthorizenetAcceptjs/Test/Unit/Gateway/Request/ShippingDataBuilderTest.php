<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\ShippingDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request\ShippingDataBuilder
 */
class ShippingDataBuilderTest extends TestCase
{
    /**
     * @var ShippingDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentDOMock;

    /**
     * @var Order
     */
    private $orderMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->paymentDOMock->method('getOrder')
            ->willReturn($this->orderMock);

        $this->builder = new ShippingDataBuilder(
            new SubjectReader()
        );
    }

    /**
     * @return void
     */
    public function testBuild()
    {
        $this->orderMock->method('getBaseShippingAmount')
            ->willReturn('43.12');

        $expected = [
            'transactionRequest' => [
                'shipping' => [
                    'amount' => '43.12',
                ],
            ],
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'order' => $this->orderMock,
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
