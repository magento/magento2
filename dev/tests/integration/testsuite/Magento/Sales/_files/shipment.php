<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\Helper\Bootstrap;

require 'default_rollback.php';
require __DIR__ . '/../../../Magento/Sales/_files/order.php';

$payment = $order->getPayment();
$paymentInfoBlock = Bootstrap::getObjectManager()->get(\Magento\Payment\Helper\Data::class)->getInfoBlock($payment);
$payment->setBlockMock($paymentInfoBlock);

$items = [];
foreach ($order->getItems() as $orderItem) {
    $items[$orderItem->getId()] = $orderItem->getQtyOrdered();
}
$shipment = Bootstrap::getObjectManager()->get(ShipmentFactory::class)->create($order, $items);
$shipment->setPackages([['1'], ['2']]);
$shipment->setShipmentStatus(\Magento\Sales\Model\Order\Shipment::STATUS_NEW);

$shipment->save();
