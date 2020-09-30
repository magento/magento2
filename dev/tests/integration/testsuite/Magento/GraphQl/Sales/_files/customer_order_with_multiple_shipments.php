<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Framework\DB\Transaction;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/customer_order_with_two_items.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Transaction $transaction */
$transaction = $objectManager->create(Transaction::class);

/** @var Order $order */
$order = $objectManager->create(Order::class)->loadByIncrementId('100000555');

$items = [];
$shipmentIds = ['0000000098', '0000000099'];
$i = 0;
foreach ($order->getItems() as $orderItem) {
    $items[$orderItem->getId()] = $orderItem->getQtyOrdered();
    /** @var Shipment $shipment */
    $shipment = $objectManager->get(ShipmentFactory::class)->create($order, $items);
    $shipment->setIncrementId($shipmentIds[$i]);
    $shipment->register();

    $transaction->addObject($shipment)->addObject($order)->save();
    $i++;
}
