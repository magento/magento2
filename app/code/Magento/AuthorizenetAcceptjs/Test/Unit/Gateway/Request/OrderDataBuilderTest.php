<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\OrderDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderDataBuilderTest extends TestCase
{
    /**
     * @var OrderDataBuilder
     */
    private $builder;

    /**
     * @var InfoInterface|MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @var OrderAdapterInterface|MockObject
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

        $expected = [
            'transactionRequest' => [
                'order' => [
                    'invoiceNumber' => '10000015'
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'order' => $this->orderMock,
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
