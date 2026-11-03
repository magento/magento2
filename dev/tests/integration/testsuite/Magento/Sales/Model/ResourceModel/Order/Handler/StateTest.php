<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Handler;

use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests order state after invoicing items with decimal quantities.
 *
 * @see State
 */
class StateTest extends TestCase
{
    /**
     * @return void
     */
    #[DataFixture('Magento/Sales/_files/order.php')]
    public function testOrderStaysProcessingAfterInvoiceOfDecimalQtyBelowOne(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Order $order */
        $order = $objectManager->create(Order::class)->loadByIncrementId('100000001');
        foreach ($order->getAllItems() as $item) {
            $item->setIsQtyDecimal(true)->setQtyOrdered(0.5);
        }

        $invoice = $objectManager->get(InvoiceService::class)->prepareInvoice($order);
        $invoice->register();
        $order->setIsInProcess(true);
        $objectManager->create(Transaction::class)
            ->addObject($invoice)
            ->addObject($order)
            ->save();

        $order = $objectManager->create(Order::class)->loadByIncrementId('100000001');
        $this->assertSame(Order::STATE_PROCESSING, $order->getState());
        $this->assertTrue($order->canShip());
    }
}
