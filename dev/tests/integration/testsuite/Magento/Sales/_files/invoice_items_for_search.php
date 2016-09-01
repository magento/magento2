<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item;
use Magento\Sales\Model\Order\Invoice\ItemFactory;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\TestFramework\Helper\Bootstrap;

require 'default_rollback.php';
require __DIR__ . '/order.php';

/** @var InvoiceManagementInterface $orderService */
$orderService = Bootstrap::getObjectManager()->create(InvoiceManagementInterface::class);
/** @var Invoice $invoice */
$invoice = $orderService->prepareInvoice($order);
$invoice->register();
/** @var Order $order */
$order = $invoice->getOrder();
$order->setIsInProcess(true);
/** @var Transaction $transactionSave */
$transactionSave = Bootstrap::getObjectManager()->create(Transaction::class);
$transactionSave->addObject($invoice)->addObject($order)->save();

/** @var ItemFactory $invoiceItemFactory */
$invoiceItemFactory = Bootstrap::getObjectManager()->create(ItemFactory::class);

$items = [
    [
        'name' => 'item 1',
        'base_price' => 10,
        'price' => 10,
        'row_total' => 10,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 2',
        'base_price' => 20,
        'price' => 20,
        'row_total' => 20,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 3',
        'base_price' => 30,
        'price' => 30,
        'row_total' => 30,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 4',
        'base_price' => 40,
        'price' => 40,
        'row_total' => 40,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 5',
        'base_price' => 50,
        'price' => 50,
        'row_total' => 50,
        'product_type' => 'simple',
        'qty' => 2,
        'qty_invoiced' => 20,
        'qty_refunded' => 2,
    ],
];

foreach ($items as $data) {
    /** @var OrderItem $orderItem */
    $orderItem = $objectManager->create(OrderItem::class);
    $orderItem->setProductId($product->getId())->setQtyOrdered(10);
    $orderItem->setBasePrice($data['base_price']);
    $orderItem->setPrice($data['price']);
    $orderItem->setRowTotal($data['row_total']);
    $orderItem->setProductType($data['product_type']);
    $orderItem->setQtyRefunded(1);
    $orderItem->setQtyInvoiced(10);
    $orderItem->setOriginalPrice(20);

    $order->addItem($orderItem);
    $order->save();

    /** @var Item $invoiceItem */
    $invoiceItem = $invoiceItemFactory->create();
    $invoiceItem->setInvoice($invoice)
        ->setName($data['name'])
        ->setOrderItemId($orderItem->getItemId())
        ->setQty($data['qty'])
        ->setPrice($data['price'])
        ->save();
}
