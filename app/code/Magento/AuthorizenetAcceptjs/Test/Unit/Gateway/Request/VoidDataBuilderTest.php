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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request\VoidDataBuilder
 */
class VoidDataBuilderTest extends TestCase
{
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->builder = new VoidDataBuilder(new SubjectReader());
    }

    /**
     * @return void
     */
    public function testBuild()
    {
        $transactionMock = $this->createMock(Transaction::class);
        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn($transactionMock);
        $transactionMock->method('getParentTxnId')
            ->willReturn('myref');

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        $expected = [
            'transactionRequest' => [
                'transactionType' =>VoidDataBuilder::REQUEST_TYPE_VOID,
                'refTransId' => 'myref',
            ],
        ];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
