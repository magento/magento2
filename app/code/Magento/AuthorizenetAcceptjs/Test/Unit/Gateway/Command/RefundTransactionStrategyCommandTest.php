<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\Command\RefundTransactionStrategyCommand;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RefundTransactionStrategyCommandTest extends TestCase
{
    /**
     * @var CommandInterface|MockObject
     */
    private $commandMock;

    /**
     * @var CommandInterface|MockObject
     */
    private $transactionDetailsCommandMock;

    /**
     * @var CommandPoolInterface|MockObject
     */
    private $commandPoolMock;

    /**
     * @var RefundTransactionStrategyCommand
     */
    private $command;

    /**
     * @var ResultInterface|MockObject
     */
    private $transactionResultMock;

    protected function setUp(): void
    {
        $this->transactionDetailsCommandMock = $this->getMockForAbstractClass(CommandInterface::class);
        $this->commandMock = $this->getMockForAbstractClass(CommandInterface::class);
        $this->transactionResultMock = $this->getMockForAbstractClass(ResultInterface::class);
        $this->commandPoolMock = $this->getMockForAbstractClass(CommandPoolInterface::class);
        $this->command = new RefundTransactionStrategyCommand(
            $this->commandPoolMock,
            new SubjectReader()
        );
    }

    public function testCommandWillVoidWhenTransactionIsPendingSettlement()
    {
        // Assert command is executed
        $this->commandMock->expects($this->once())
            ->method('execute');

        $this->commandPoolMock->method('get')
            ->willReturnMap(
                [
                    [
                        'get_transaction_details',
                        $this->transactionDetailsCommandMock
                    ],
                    [
                        'void',
                        $this->commandMock
                    ]
                ]
            );

        $this->transactionResultMock->method('get')
            ->willReturn(
                [
                    'transaction' => [
                        'transactionStatus' => 'capturedPendingSettlement',
                        'authAmount' => '20.19',
                    ]
                ]
            );

        $buildSubject = [
            'foo' => '123',
            'amount' => '20.19',
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->command->execute($buildSubject);
    }

    /**
     */
    public function testCommandWillThrowExceptionWhenVoidTransactionIsPartial()
    {
        $this->expectException(\Magento\Payment\Gateway\Command\CommandException::class);
        $this->expectExceptionMessage('The transaction has not been settled, a partial refund is not yet available.');

        // Assert command is executed
        $this->commandMock->expects($this->never())
            ->method('execute');

        $this->commandPoolMock->method('get')
            ->willReturnMap(
                [
                    [
                        'get_transaction_details',
                        $this->transactionDetailsCommandMock
                    ],
                ]
            );

        $this->transactionResultMock->method('get')
            ->willReturn(
                [
                    'transaction' => [
                        'transactionStatus' => 'capturedPendingSettlement',
                        'authAmount' => '20.19',
                    ]
                ]
            );

        $buildSubject = [
            'foo' => '123',
            'amount' => '10.19',
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->command->execute($buildSubject);
    }

    public function testCommandWillRefundWhenTransactionIsSettled()
    {
        // Assert command is executed
        $this->commandMock->expects($this->once())
            ->method('execute');

        $this->commandPoolMock->method('get')
            ->willReturnMap(
                [
                    [
                        'get_transaction_details',
                        $this->transactionDetailsCommandMock
                    ],
                    [
                        'refund_settled',
                        $this->commandMock
                    ]
                ]
            );

        $this->transactionResultMock->method('get')
            ->willReturn(
                [
                    'transaction' => [
                        'transactionStatus' => 'settledSuccessfully'
                    ]
                ]
            );

        $buildSubject = [
            'foo' => '123'
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->command->execute($buildSubject);
    }

    /**
     */
    public function testCommandWillThrowExceptionWhenTransactionIsInInvalidState()
    {
        $this->expectException(\Magento\Payment\Gateway\Command\CommandException::class);
        $this->expectExceptionMessage('This transaction cannot be refunded with its current status.');

        // Assert command is never executed
        $this->commandMock->expects($this->never())
            ->method('execute');

        $this->commandPoolMock->method('get')
            ->willReturnMap(
                [
                    [
                        'get_transaction_details',
                        $this->transactionDetailsCommandMock
                    ],
                ]
            );

        $this->transactionResultMock->method('get')
            ->willReturn(
                [
                    'transaction' => [
                        'transactionStatus' => 'somethingIsWrong'
                    ]
                ]
            );

        $buildSubject = [
            'foo' => '123'
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->command->execute($buildSubject);
    }
}
