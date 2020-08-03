<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Sales/_files/order.php';

/** @var Order $order */
$items = [];
foreach ($order->getItems() as $orderItem) {
    $items[$orderItem->getId()] = $orderItem->getQtyOrdered();
}

/** @var Shipment $shipment */
$shipment = Bootstrap::getObjectManager()->get(ShipmentFactory::class)->create($order, $items);
$shipment->setPackages([['1'], ['2']]);
$shipment->setShipmentStatus(Shipment::STATUS_NEW);
$shipment->save();

/** @var Track $track */
$track = Bootstrap::getObjectManager()->create(Track::class);
$track->setOrderId($order->getId());
$track->setParentId($shipment->getId());
$track->setTitle('Shipment Title');
$track->setCarrierCode(Track::CUSTOM_CARRIER_CODE);
$track->setTrackNumber('track_number');
$track->setDescription('Description of shipment');

/** @var ShipmentTrackRepositoryInterface $shipmentTrackRepository */
$shipmentTrackRepository = Bootstrap::getObjectManager()->get(ShipmentTrackRepositoryInterface::class);
$shipmentTrackRepository->save($track);
