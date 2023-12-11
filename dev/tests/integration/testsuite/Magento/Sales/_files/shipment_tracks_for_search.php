<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Payment\Helper\Data;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/default_rollback.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$payment = $order->getPayment();
$paymentInfoBlock = Bootstrap::getObjectManager()->get(Data::class)
    ->getInfoBlock($payment);
$payment->setBlockMock($paymentInfoBlock);

$items = [];
foreach ($order->getItems() as $orderItem) {
    $items[$orderItem->getId()] = $orderItem->getQtyOrdered();
}
$shipment = $objectManager->get(ShipmentFactory::class)->create($order, $items);
$shipment->setPackages([['1'], ['2']]);
$shipment->setShipmentStatus(\Magento\Sales\Model\Order\Shipment::STATUS_NEW);
$shipment->save();

$tracks = [
    [
        'title' => 'title 1',
        'carrier_code' => 'carrier code 1',
        'track_number' => 'track number 1',
        'description' => 'description 1',
        'qty' => 1,
        'weight' => 1,
    ],
    [
        'title' => 'title 2',
        'carrier_code' => 'carrier code 2',
        'track_number' => 'track number 2',
        'description' => 'description 2',
        'qty' => 2,
        'weight' => 1,
    ],
    [
        'title' => 'title 3',
        'carrier_code' => 'carrier code 3',
        'track_number' => 'track number 3',
        'description' => 'description 3',
        'qty' => 3,
        'weight' => 1,
    ],
    [
        'title' => 'title 4',
        'carrier_code' => 'carrier code 4',
        'track_number' => 'track number 4',
        'description' => 'description 4',
        'qty' => 4,
        'weight' => 1,
    ],
    [
        'title' => 'title 5',
        'carrier_code' => 'carrier code 5',
        'track_number' => 'track number 5',
        'description' => 'description 5',
        'qty' => 5,
        'weight' => 2,
    ],
];

/** @var ShipmentTrackRepositoryInterface $shipmentTrackRepository */
$shipmentTrackRepository = $objectManager->get(ShipmentTrackRepositoryInterface::class);

foreach ($tracks as $data) {
    /** @var $track Track */
    $track = $objectManager->create(Track::class);
    $track->setOrderId($order->getId());
    $track->setParentId($shipment->getId());
    $track->setTitle($data['title']);
    $track->setCarrierCode($data['carrier_code']);
    $track->setTrackNumber($data['track_number']);
    $track->setDescription($data['description']);
    $track->setQty($data['qty']);
    $track->setWeight($data['weight']);
    $shipmentTrackRepository->save($track);
}
