<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory;
use Magento\Sales\Api\InvoiceOrderInterface;

require __DIR__ . '/../../../Magento/Sales/_files/customer_order_with_two_items.php';

/** @var InvoiceItemCreationInterfaceFactory $invoiceItemFactory */
$invoiceItemFactory = $objectManager->get(InvoiceItemCreationInterfaceFactory::class);
/** @var InvoiceOrderInterface $invoiceOrder */
$invoiceOrder = $objectManager->get(InvoiceOrderInterface::class);

foreach ($order->getItems() as $orderItem) {
    $invoiceItem = $invoiceItemFactory->create();
    $invoiceItem->setOrderItemId($orderItem->getItemId());
    $invoiceItem->setQty($orderItem->getQtyOrdered());
    $invoiceOrder->execute($order->getId(), false, [$invoiceItem]);
}
