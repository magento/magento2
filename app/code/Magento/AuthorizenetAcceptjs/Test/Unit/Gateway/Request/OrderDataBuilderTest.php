<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\OrderDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\Order\Payment;

class OrderDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderDataBuilder
     */
    private $builder;

    /**
     * @var InfoInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentDataObjectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentDOMock;

    /**
     * @var OrderAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->paymentDOMock->method('getOrder')
            ->willReturn($this->orderMock);

        $this->builder = new OrderDataBuilder(new SubjectReader());
    }

    public function testBuild()
    {
        $this->orderMock->method('getOrderIncrementId')
            ->willReturn('10000015');
        $this->paymentMock->method('getPoNumber')
            ->willReturn('abc');

        $expected = [
            'transactionRequest' => [
                'order' => [
                    'invoiceNumber' => '10000015'
                ],
                'poNumber' => 'abc'
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
