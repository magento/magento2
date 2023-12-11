<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Sales\Api\ShipmentCommentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment\Comment;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/customer_order_with_two_items.php');

$objectManager = Bootstrap::getObjectManager();
/** @var Transaction $transaction */
$transaction = $objectManager->create(Transaction::class);

/** @var Order $order */
$order = $objectManager->create(Order::class)->loadByIncrementId('100000555');

$items = [];
foreach ($order->getItems() as $orderItem) {
    $items[$orderItem->getId()] = $orderItem->getQtyOrdered();
}
$shipment = $objectManager->get(ShipmentFactory::class)->create($order, $items);
$shipment->register();

$transaction->addObject($shipment)->addObject($order)->save();

//Add shipment comments
$shipmentCommentRepository = $objectManager->get(ShipmentCommentRepositoryInterface::class);
$comments = [
    [
        'comment' => 'This comment is visible to the customer',
        'is_visible_on_front' => 1,
        'is_customer_notified' => 1,
    ],
    [
        'comment' => 'This comment should not be visible to the customer',
        'is_visible_on_front' => 0,
        'is_customer_notified' => 0,
    ],
];

foreach ($comments as $commentData) {
    /** @var Comment $comment */
    $comment = $objectManager->create(Comment::class);
    $comment->setParentId($shipment->getId());
    $comment->setComment($commentData['comment']);
    $comment->setIsVisibleOnFront($commentData['is_visible_on_front']);
    $comment->setIsCustomerNotified($commentData['is_customer_notified']);
    $shipmentCommentRepository->save($comment);
}

//Add tracking
/** @var ShipmentTrackRepositoryInterface $shipmentTrackRepository */
$shipmentTrackRepository = $objectManager->get(ShipmentTrackRepositoryInterface::class);
/** @var Track $track */
$track = $objectManager->create(Track::class);
$track->setOrderId($order->getId());
$track->setParentId($shipment->getId());
$track->setTitle('United Parcel Service');
$track->setCarrierCode('ups');
$track->setTrackNumber('1234567890');
$shipmentTrackRepository->save($track);
