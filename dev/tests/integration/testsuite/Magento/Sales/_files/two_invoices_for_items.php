<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\InvoiceItemInterfaceFactory;
use Magento\Sales\Api\InvoiceItemRepositoryInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

require __DIR__ . '/../../../Magento/Sales/_files/order_two_items_by_customer.php';

/** @var InvoiceManagementInterface $invoiceManagement */
$invoiceManagement = $objectManager->get(InvoiceManagementInterface::class);
/** @var InvoiceRepositoryInterface $invoiceRepository */
$invoiceRepository = $objectManager->get(InvoiceRepositoryInterface::class);
/** @var InvoiceItemInterfaceFactory $invoiceItemFactory */
$invoiceItemFactory = $objectManager->get(InvoiceItemInterfaceFactory::class);
/** @var InvoiceItemRepositoryInterface $invoiceItemRepository */
$invoiceItemRepository = $objectManager->get(InvoiceItemRepositoryInterface::class);
/** @var OrderItemRepositoryInterface $orderItemRepository */
$orderItemRepository = $objectManager->get(OrderItemRepositoryInterface::class);

foreach ($order->getItems() as $orderItem) {
    $invoice = $invoiceManagement->prepareInvoice($order, [$orderItem->getId() => $orderItem->getQtyOrdered()]);
    $invoice->register();
    $invoice->setSubtotal(10);
    $invoice = $invoiceRepository->save($invoice);

    $orderItem->setQtyInvoiced($orderItem->getQtyOrdered());
    $orderItemRepository->save($orderItem);
}
$order->setTotalPaid(30);
$order->setTotalInvoiced(30);
$orderRepository->save($order);
