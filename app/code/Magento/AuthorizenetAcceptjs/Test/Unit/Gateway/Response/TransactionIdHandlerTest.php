<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Response\TransactionIdHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionIdHandlerTest extends TestCase
{
    /**
     * @var TransactionIdHandler
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

        $this->builder = new TransactionIdHandler(new SubjectReader());
    }

    public function testHandleDefaultResponse()
    {
        $this->paymentMock->method('getParentTransactionId')
            ->willReturn(null);
        // Assert the id is set
        $this->paymentMock->expects($this->once())
            ->method('setTransactionId')
            ->with('thetransid');
        // Assert the id is set in the additional info for later
        $this->paymentMock->expects($this->once())
            ->method('setTransactionAdditionalInfo')
            ->with('real_transaction_id', 'thetransid');

        $response = [
            'transactionResponse' => [
                'transId' => 'thetransid',
            ]
        ];
        $subject = [
            'payment' => $this->paymentDOMock
        ];

        $this->builder->handle($subject, $response);
        // Assertions are part of mocking above
    }

    public function testHandleDifferenceInTransactionId()
    {
        $this->paymentMock->method('getParentTransactionId')
            ->willReturn('somethingElse');
        // Assert the id is set
        $this->paymentMock->expects($this->once())
            ->method('setTransactionId')
            ->with('thetransid');

        $response = [
            'transactionResponse' => [
                'transId' => 'thetransid',
            ]
        ];
        $subject = [
            'payment' => $this->paymentDOMock
        ];

        $this->builder->handle($subject, $response);
        // Assertions are part of mocking above
    }
}
