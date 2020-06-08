<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/customer_order_with_two_items.php');

$objectManager = Bootstrap::getObjectManager();
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000555');
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
