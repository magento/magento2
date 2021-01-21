<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\VoidDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VoidDataBuilderTest extends TestCase
{
    private const REQUEST_TYPE_VOID = 'voidTransaction';

    /**
     * @var VoidDataBuilder
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

        $this->builder = new VoidDataBuilder(new SubjectReader());
    }

    public function testBuild()
    {
        $transactionMock = $this->createMock(Transaction::class);
        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn($transactionMock);
        $transactionMock->method('getParentTxnId')
            ->willReturn('myref');

        $buildSubject = [
            'payment' => $this->paymentDOMock
        ];

        $expected = [
            'transactionRequest' => [
                'transactionType' => self::REQUEST_TYPE_VOID,
                'refTransId' => 'myref',
            ]
        ];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
