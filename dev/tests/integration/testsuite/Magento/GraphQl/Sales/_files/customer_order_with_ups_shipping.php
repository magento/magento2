<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_with_different_types_of_product.php');

$objectManager = Bootstrap::getObjectManager();
/** @var ShipOrderInterface $shipOrder */
$shipOrder = $objectManager->create(ShipOrderInterface::class);
/** @var Order $order */
$order = $objectManager->create(Order::class)->loadByIncrementId('100000001');
//Set the shipping method
$order->setShippingDescription('UPS Next Day Air');
$order->setShippingMethod('ups_01');
$order->save();

//Create Shipment with UPS tracking and some items
$shipmentItems = [];
foreach ($order->getItems() as $orderItem) {
    if (count($shipmentItems) === 2) {
        break;
    }
    /** @var ShipmentItemCreationInterface $shipmentItem */
    $shipmentItem = $objectManager->create(ShipmentItemCreationInterface::class);
    $shipmentItem->setOrderItemId($orderItem->getItemId());
    $shipmentItem->setQty($orderItem->getQtyOrdered());
    $shipmentItems[] = $shipmentItem;
}
/** @var ShipmentTrackCreationInterface $track */
$track = $objectManager->create(ShipmentTrackCreationInterface::class);
$track->setCarrierCode('ups');
$track->setTitle('United Parcel Service');
$track->setTrackNumber('987654321');
$shipOrder->execute($order->getEntityId(), $shipmentItems, false, false, null, [$track]);

//Create second Shipment
$shipmentItems = [];
foreach ($order->getItems() as $orderItem) {
    if ($orderItem->getQtyShipped() === 0) {
        /** @var ShipmentItemCreationInterface $shipmentItem */
        $shipmentItem = $objectManager->create(ShipmentItemCreationInterface::class);
        $shipmentItem->setOrderItemId($orderItem->getItemId());
        $shipmentItem->setQty($orderItem->getQtyOrdered());
        $shipmentItems[] = $shipmentItem;
    }
}
$shipOrder->execute($order->getEntityId(), $shipmentItems);
