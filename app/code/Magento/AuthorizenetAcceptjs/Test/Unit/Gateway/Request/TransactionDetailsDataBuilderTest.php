<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\TransactionDetailsDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Sales\Model\Order\Payment\Transaction;
use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;

class TransactionDetailsDataBuilderTest extends TestCase
{
    /**
     * @var TransactionDetailsDataBuilder
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

        $this->builder = new TransactionDetailsDataBuilder(new SubjectReader());
    }

    public function testBuild()
    {
        $transactionMock = $this->createMock(Transaction::class);

        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn($transactionMock);

        $transactionMock->method('getParentTxnId')
            ->willReturn('foo');

        $expected = [
            'transId' => 'foo'
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }

    public function testBuildWithIncludedTransactionId()
    {
        $transactionMock = $this->createMock(Transaction::class);

        $this->paymentMock->expects($this->never())
            ->method('getAuthorizationTransaction');

        $transactionMock->expects($this->never())
            ->method('getParentTxnId');

        $expected = [
            'transId' => 'foo'
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'transactionId' => 'foo'
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
