<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\Command\TransactionReviewUpdateCommand;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionReviewUpdateCommandTest extends TestCase
{
    /**
     * @var CommandInterface|MockObject
     */
    private $transactionDetailsCommandMock;

    /**
     * @var CommandPoolInterface|MockObject
     */
    private $commandPoolMock;

    /**
     * @var TransactionReviewUpdateCommand
     */
    private $command;

    /**
     * @var ResultInterface|MockObject
     */
    private $transactionResultMock;

    /**
     * @var PaymentDataObject|MockObject
     */
    private $paymentDOMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObject::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->transactionDetailsCommandMock = $this->createMock(CommandInterface::class);
        $this->transactionResultMock = $this->createMock(ResultInterface::class);
        $this->commandPoolMock = $this->createMock(CommandPoolInterface::class);
        $this->command = new TransactionReviewUpdateCommand(
            $this->commandPoolMock,
            new SubjectReader()
        );
    }

    public function testCommandWillMarkTransactionAsApprovedWhenNotVoid()
    {
        $this->commandPoolMock->method('get')
            ->willReturnMap([
                ['get_transaction_details', $this->transactionDetailsCommandMock],
            ]);

        $this->transactionResultMock->method('get')
            ->willReturn([
                'transaction' => [
                    'transactionStatus' => 'authorizedPendingCapture'
                ]
            ]);

        // Assert payment is handled correctly
        $this->paymentMock->expects($this->exactly(2))
            ->method('setData')
            ->withConsecutive(
                ['is_transaction_denied', false],
                ['is_transaction_approved', true]
            );

        $buildSubject = [
            'payment' => $this->paymentDOMock
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->command->execute($buildSubject);
    }

    /**
     * @dataProvider declinedTransactionStatusesProvider
     * @param string $status
     */
    public function testCommandWillMarkTransactionAsDeniedWhenDeclined(string $status)
    {
        $this->commandPoolMock->method('get')
            ->willReturnMap([
                ['get_transaction_details', $this->transactionDetailsCommandMock],
            ]);

        $this->transactionResultMock->method('get')
            ->willReturn([
                'transaction' => [
                    'transactionStatus' => $status
                ]
            ]);

        // Assert payment is handled correctly
        $this->paymentMock->expects($this->exactly(2))
            ->method('setData')
            ->withConsecutive(
                ['is_transaction_denied', true],
                ['is_transaction_approved', false]
            );

        $buildSubject = [
            'payment' => $this->paymentDOMock
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->command->execute($buildSubject);
    }

    /**
     * @dataProvider pendingTransactionStatusesProvider
     * @param string $status
     */
    public function testCommandWillDoNothingWhenTransactionIsStillPending(string $status)
    {
        $this->commandPoolMock->method('get')
            ->willReturnMap([
                ['get_transaction_details', $this->transactionDetailsCommandMock],
            ]);

        $this->transactionResultMock->method('get')
            ->willReturn([
                'transaction' => [
                    'transactionStatus' => $status
                ]
            ]);

        // Assert payment is handled correctly
        $this->paymentMock->expects($this->never())
            ->method('setData');

        $buildSubject = [
            'payment' => $this->paymentDOMock
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->command->execute($buildSubject);
    }

    public function pendingTransactionStatusesProvider()
    {
        return [
            ['FDSPendingReview'],
            ['FDSAuthorizedPendingReview']
        ];
    }

    public function declinedTransactionStatusesProvider()
    {
        return [
            ['void'],
            ['declined']
        ];
    }
}
