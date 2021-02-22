<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Response\PaymentReviewStatusHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentReviewStatusHandlerTest extends TestCase
{
    /**
     * @var PaymentReviewStatusHandler
     */
    private $handler;

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

        $this->handler = new PaymentReviewStatusHandler(new SubjectReader());
    }

    public function testApprovesPayment()
    {
        $subject = [
            'payment' => $this->paymentDOMock
        ];
        $response = [
            'transaction' => [
                'transactionStatus' => 'approvedOrSomething',
            ]
        ];

        // Assert payment is handled correctly
        $this->paymentMock->expects($this->exactly(2))
            ->method('setData')
            ->withConsecutive(
                ['is_transaction_denied', false],
                ['is_transaction_approved', true]
            );

        $this->handler->handle($subject, $response);
        // Assertions are via mock expects above
    }

    /**
     * @param string $status
     * @dataProvider declinedTransactionStatusesProvider
     */
    public function testDeniesPayment(string $status)
    {
        $subject = [
            'payment' => $this->paymentDOMock
        ];
        $response = [
            'transaction' => [
                'transactionStatus' => $status,
            ]
        ];

        // Assert payment is handled correctly
        $this->paymentMock->expects($this->exactly(2))
            ->method('setData')
            ->withConsecutive(
                ['is_transaction_denied', true],
                ['is_transaction_approved', false]
            );
        $this->handler->handle($subject, $response);
    }

    /**
     * @param string $status
     * @dataProvider pendingTransactionStatusesProvider
     */
    public function testDoesNothingWhenPending(string $status)
    {
        $subject = [
            'payment' => $this->paymentDOMock
        ];
        $response = [
            'transaction' => [
                'transactionStatus' => $status,
            ]
        ];

        // Assert payment is handled correctly
        $this->paymentMock->expects($this->never())
            ->method('setData');

        $this->handler->handle($subject, $response);
    }

    /**
     * @return array
     */
    public function pendingTransactionStatusesProvider()
    {
        return [
            ['FDSPendingReview'],
            ['FDSAuthorizedPendingReview']
        ];
    }

    /**
     * @return array
     */
    public function declinedTransactionStatusesProvider()
    {
        return [
            ['void'],
            ['declined']
        ];
    }
}
