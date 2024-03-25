<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Plugin\Model\Service\Invoice;

use Magento\Framework\DB\Transaction;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Plugin\Model\Service\Invoice\AddTransactionCommentAfterCapture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test to add transaction comment to the order after capture invoice
 */
class AddTransactionCommentAfterCaptureTest extends TestCase
{
    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    private $invoiceRepository;

    /**
     * @var TransactionFactory|MockObject
     */
    private $transactionFactory;

    /**
     * @var AddTransactionCommentAfterCapture
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->transactionFactory = $this->createMock(TransactionFactory::class);

        $this->plugin = new AddTransactionCommentAfterCapture(
            $this->invoiceRepository,
            $this->transactionFactory
        );
    }

    /**
     * Test to add transaction comment after capture invoice
     */
    public function testPlugin(): void
    {
        $result = true;
        $invoiceId = 3;

        $orderMock = $this->createMock(Order::class);
        $invoiceMock = $this->createMock(Invoice::class);
        $invoiceMock->method('getOrder')->willReturn($orderMock);
        $this->invoiceRepository->method('get')->with($invoiceId)->willReturn($invoiceMock);

        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock
            ->method('addObject')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$invoiceMock] => $transactionMock,
                [$orderMock] => $transactionMock
            });
        $transactionMock->expects($this->once())->method('save');
        $this->transactionFactory->method('create')->willReturn($transactionMock);

        /** @var InvoiceService $invoiceService */
        $invoiceService = $this->createMock(InvoiceService::class);

        $this->assertEquals(
            $result,
            $this->plugin->afterSetCapture($invoiceService, $result, $invoiceId)
        );
    }
}
