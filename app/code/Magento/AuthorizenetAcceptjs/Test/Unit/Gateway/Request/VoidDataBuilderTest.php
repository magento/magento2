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

class VoidDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    private const REQUEST_TYPE_VOID = 'voidTransaction';

    /**
     * @var VoidDataBuilder
     */
    private $builder;

    /**
     * @var Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentDOMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->builder = new VoidDataBuilder(new SubjectReader());
    }

    public function testBuild()
    {
        $transactionMock = $this->createMock(Payment\Transaction::class);
        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn($transactionMock);
        $transactionMock->method('getAdditionalInformation')
            ->with('real_transaction_id')
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
