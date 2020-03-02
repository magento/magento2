<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/order_with_customer.php';

$objectManager = Bootstrap::getObjectManager();
$order->setIsInProcess(true);
/** @var Transaction $transaction */
$transaction = $objectManager->create(Transaction::class);

$items = [];
foreach ($order->getItems() as $orderItem) {
    $items[$orderItem->getId()] = $orderItem->getQtyOrdered();
}

$shipment = $objectManager->get(ShipmentFactory::class)->create($order, $items);
$shipment->register();

$transaction->addObject($shipment)->addObject($order)->save();
