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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Response\PaymentReviewStatusHandler
 */
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->handler = new PaymentReviewStatusHandler(new SubjectReader());
    }

    /**
     * @return void
     */
    public function testApprovesPayment()
    {
        $subject = [
            'payment' => $this->paymentDOMock,
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
     * @return void
     */
    public function testDeniesPayment(string $status)
    {
        $subject = [
            'payment' => $this->paymentDOMock,
        ];
        $response = [
            'transaction' => [
                'transactionStatus' => $status,
            ],
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
     * @return void
     */
    public function testDoesNothingWhenPending(string $status)
    {
        $subject = [
            'payment' => $this->paymentDOMock,
        ];
        $response = [
            'transaction' => [
                'transactionStatus' => $status,
            ],
        ];

        // Assert payment is handled correctly
        $this->paymentMock->expects($this->never())
            ->method('setData');

        $this->handler->handle($subject, $response);
    }

    /**
     * @return array
     */
    public function pendingTransactionStatusesProvider(): array
    {
        return [
            ['FDSPendingReview'],
            ['FDSAuthorizedPendingReview'],
        ];
    }

    /**
     * @return array
     */
    public function declinedTransactionStatusesProvider(): array
    {
        return [
            ['void'],
            ['declined'],
        ];
    }
}
