<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');

$shipments = [
    [
        'increment_id' => '100000001',
        'shipping_address_id' => 1,
        'shipment_status' => \Magento\Sales\Model\Order\Shipment::STATUS_NEW,
        'store_id' => 1,
        'shipping_label' => 'shipping_label_100000001',
    ],
    [
        'increment_id' => '100000002',
        'shipping_address_id' => 3,
        'shipment_status' => \Magento\Sales\Model\Order\Shipment::STATUS_NEW,
        'store_id' => 1,
        'shipping_label' => 'shipping_label_100000002',
    ],
    [
        'increment_id' => '100000003',
        'shipping_address_id' => 3,
        'shipment_status' => \Magento\Sales\Model\Order\Shipment::STATUS_NEW,
        'store_id' => 1,
        'shipping_label' => 'shipping_label_100000003',
    ],
    [
        'increment_id' => '100000004',
        'shipping_address_id' => 4,
        'shipment_status' => 'closed',
        'store_id' => 1,
        'shipping_label' => 'shipping_label_100000004',
    ],
];

/** @var array $shipmentData */
foreach ($shipments as $shipmentData) {
    $items = [];
    foreach ($order->getItems() as $orderItem) {
        $items[$orderItem->getId()] = $orderItem->getQtyOrdered();
    }
    /** @var \Magento\Sales\Model\Order\Shipment $shipment */
    $shipment = Bootstrap::getObjectManager()->get(ShipmentFactory::class)->create($order, $items);
    $shipment->setIncrementId($shipmentData['increment_id']);
    $shipment->setShippingAddressId($shipmentData['shipping_address_id']);
    $shipment->setShipmentStatus($shipmentData['shipment_status']);
    $shipment->setStoreId($shipmentData['store_id']);
    $shipment->setShippingLabel($shipmentData['shipping_label']);
    $shipment->save();
}
