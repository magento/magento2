<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;

require __DIR__ . '/../../../Magento/Sales/_files/order.php';
/** @var \Magento\Catalog\Model\Product $product */

/** @var OrderItem $orderItem */
$orderItem = $objectManager->create(OrderItem::class);
$orderItem->setProductId($product->getId())
    ->setQtyOrdered(2)
    ->setBasePrice($product->getPrice())
    ->setPrice($product->getPrice())
    ->setRowTotal($product->getPrice())
    ->setProductType('simple')
    ->setName($product->getName())
    ->setFreeShipping('1');

/** @var Order $order */
$order->setShippingDescription('Flat Rate - Fixed')
    ->setShippingAmount(0)
    ->setCouponCode('1234567890')
    ->setDiscountDescription('1234567890')
    ->addItem($orderItem);

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
$orderRepository->save($order);
