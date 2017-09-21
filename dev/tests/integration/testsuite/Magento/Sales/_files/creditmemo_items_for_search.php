<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\CreditmemoItemRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\Creditmemo\ItemFactory;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\TestFramework\Helper\Bootstrap;

require 'default_rollback.php';
require __DIR__ . '/order.php';

$orderCollection = Bootstrap::getObjectManager()->create(Order::class)->getCollection();
/** @var \Magento\Sales\Model\Order $order */
$order = $orderCollection->getFirstItem();

/** @var ItemFactory $creditmemoItemFactory */
$creditmemoItemFactory = Bootstrap::getObjectManager()->create(ItemFactory::class);
/** @var CreditmemoFactory $creditmemoFactory */
$creditmemoFactory = Bootstrap::getObjectManager()->get(CreditmemoFactory::class);
/** @var Creditmemo $creditmemo */
$creditmemo = $creditmemoFactory->createByOrder($order, $order->getData());
$creditmemo->setOrder($order);
$creditmemo->setState(Creditmemo::STATE_OPEN);
$creditmemo->save();

$items = [
    [
        'name' => 'item 1',
        'base_price' => 10,
        'price' => 10,
        'row_total' => 10,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 2',
        'base_price' => 20,
        'price' => 20,
        'row_total' => 20,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 3',
        'base_price' => 30,
        'price' => 30,
        'row_total' => 30,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 4',
        'base_price' => 40,
        'price' => 40,
        'row_total' => 40,
        'product_type' => 'simple',
        'qty' => 10,
        'qty_invoiced' => 10,
        'qty_refunded' => 1,
    ],
    [
        'name' => 'item 5',
        'base_price' => 50,
        'price' => 50,
        'row_total' => 50,
        'product_type' => 'simple',
        'qty' => 2,
        'qty_invoiced' => 20,
        'qty_refunded' => 2,
    ],
];

/** @var CreditmemoItemRepositoryInterface $creditmemoItemRepository */
$creditmemoItemRepository = $objectManager->get(CreditmemoItemRepositoryInterface::class);

foreach ($items as $data) {
    /** @var OrderItem $orderItem */
    $orderItem = $objectManager->create(OrderItem::class);
    $orderItem->setProductId($product->getId())->setQtyOrdered(10);
    $orderItem->setBasePrice($data['base_price']);
    $orderItem->setPrice($data['price']);
    $orderItem->setRowTotal($data['row_total']);
    $orderItem->setProductType($data['product_type']);
    $orderItem->setQtyRefunded(1);
    $orderItem->setQtyInvoiced(10);
    $orderItem->setOriginalPrice(20);

    $order->addItem($orderItem);
    $order->save();

    /** @var Item $creditmemoItem */
    $creditmemoItem = $creditmemoItemFactory->create();
    $creditmemoItem->setCreditmemo($creditmemo)
        ->setName($data['name'])
        ->setOrderItemId($orderItem->getItemId())
        ->setQty($data['qty'])
        ->setPrice($data['price']);

    $creditmemoItemRepository->save($creditmemoItem);
}
