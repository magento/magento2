<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\Command\FetchTransactionInfoCommand;
use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FetchTransactionInfoCommandTest extends TestCase
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
     * @var FetchTransactionInfoCommand
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

    /**
     * @var Config
     */
    private $configMock;

    /**
     * @var HandlerInterface
     */
    private $handlerMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObject::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->configMock = $this->createMock(Config::class);
        $this->configMock->method('getTransactionInfoSyncKeys')
            ->willReturn(['foo', 'bar']);
        $orderMock = $this->createMock(Order::class);
        $this->paymentDOMock->method('getOrder')
            ->willReturn($orderMock);
        $this->transactionDetailsCommandMock = $this->createMock(CommandInterface::class);
        $this->transactionResultMock = $this->createMock(ResultInterface::class);
        $this->commandPoolMock = $this->createMock(CommandPoolInterface::class);
        $this->handlerMock = $this->createMock(HandlerInterface::class);
        $this->command = new FetchTransactionInfoCommand(
            $this->commandPoolMock,
            new SubjectReader(),
            $this->configMock,
            $this->handlerMock
        );
    }

    public function testCommandWillMarkTransactionAsApprovedWhenNotVoid()
    {
        $response = [
            'transaction' => [
                'transactionStatus' => 'authorizedPendingCapture',
                'foo' => 'abc',
                'bar' => 'cba',
                'dontreturnme' => 'justdont'
            ]
        ];

        $this->commandPoolMock->method('get')
            ->willReturnMap([
                ['get_transaction_details', $this->transactionDetailsCommandMock],
            ]);

        $this->transactionResultMock->method('get')
            ->willReturn($response);

        $buildSubject = [
            'payment' => $this->paymentDOMock
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->with($buildSubject, $response)
            ->willReturn($this->transactionResultMock);

        $result = $this->command->execute($buildSubject);

        $expected = [
            'foo' => 'abc',
            'bar' => 'cba'
        ];

        $this->assertSame($expected, $result);
    }
}
