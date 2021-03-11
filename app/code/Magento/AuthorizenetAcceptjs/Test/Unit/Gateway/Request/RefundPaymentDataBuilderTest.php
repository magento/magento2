<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\RefundPaymentDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RefundPaymentDataBuilderTest extends TestCase
{
    /**
     * @var RefundPaymentDataBuilder
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

    protected function setUp(): void
    {
        $this->paymentDOMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->builder = new RefundPaymentDataBuilder(
            new SubjectReader()
        );
    }

    public function testBuild()
    {
        $this->paymentMock->method('getAdditionalInformation')
            ->with('ccLast4')
            ->willReturn('1111');

        $expected = [
            'transactionRequest' => [
                'payment' => [
                    'creditCard' => [
                        'cardNumber' => '1111',
                        'expirationDate' => 'XXXX'
                    ]
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => 123.45
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
