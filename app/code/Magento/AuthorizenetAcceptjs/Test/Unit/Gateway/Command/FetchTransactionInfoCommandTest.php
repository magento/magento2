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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Command\FetchTransactionInfoCommand
 */
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
     * @var Config
     */
    private $configMock;

    /**
     * @var HandlerInterface
     */
    private $handlerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $paymentMock = $this->createMock(Payment::class);
        $orderMock = $this->createMock(Order::class);

        $this->paymentDOMock = $this->createMock(PaymentDataObject::class);
        $this->paymentDOMock
            ->method('getPayment')
            ->willReturn($paymentMock);
        $this->configMock = $this->createMock(Config::class);
        $this->configMock
            ->method('getTransactionInfoSyncKeys')
            ->willReturn(['foo', 'bar']);
        $this->paymentDOMock
            ->method('getOrder')
            ->willReturn($orderMock);
        $this->transactionDetailsCommandMock = $this->createMock(CommandInterface::class);
        $this->transactionResultMock = $this->createMock(ResultInterface::class);
        $this->commandPoolMock = $this->createMock(CommandPoolInterface::class);
        $this->handlerMock = $this->createMock(HandlerInterface::class);

        $this->command = $objectManagerHelper->getObject(
            FetchTransactionInfoCommand::class,
            [
                'commandPool' => $this->commandPoolMock,
                'subjectReader' => new SubjectReader(),
                'config' => $this->configMock,
                'handler' => $this->handlerMock,
            ]
        );
    }

    /**
     * @dataProvider transactionDataProvider
     * @param array $response
     * @param array $expected
     * @return void
     */
    public function testCommandWillMarkTransactionAsApprovedWhenNotVoid(array $response, array $expected)
    {
        $this->commandPoolMock
            ->method('get')
            ->willReturnMap([
                ['get_transaction_details', $this->transactionDetailsCommandMock],
            ]);
        $this->transactionResultMock
            ->method('get')
            ->willReturn($response);
        $buildSubject = ['payment' => $this->paymentDOMock];
        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);
        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->with($buildSubject, $response)
            ->willReturn($this->transactionResultMock);

        $result = $this->command->execute($buildSubject);
        $this->assertSame($expected, $result->get());
    }

    /**
     * @return array
     */
    public function transactionDataProvider(): array
    {
        return [
            [
                'response' => [
                    'transaction' => [
                        'transactionStatus' => 'authorizedPendingCapture',
                        'foo' => 'abc',
                        'bar' => 'cba',
                        'dontreturnme' => 'justdont',
                    ],
                ],
                'expected' => [
                    'foo' => 'abc',
                    'bar' => 'cba',
                ],
            ],
        ];
    }
}
