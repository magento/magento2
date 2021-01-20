<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\PoDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PoDataBuilderTest extends TestCase
{
    /**
     * @var PoDataBuilder
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

    protected function setUp(): void
    {
        $this->paymentDOMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->builder = new PoDataBuilder(new SubjectReader());
    }

    public function testBuild()
    {
        $this->paymentMock->method('getPoNumber')
            ->willReturn('abc');

        $expected = [
            'transactionRequest' => [
                'poNumber' => 'abc'
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
