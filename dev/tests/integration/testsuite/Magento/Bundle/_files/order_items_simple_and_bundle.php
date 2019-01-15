<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/order_item_with_bundle_and_options.php';
require __DIR__ . '/../../../Magento/Catalog/_files/category_product.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Order\Item $orderItem */
$orderItem = $objectManager->create(\Magento\Sales\Model\Order\Item::class);
/** @var $product \Magento\Catalog\Model\Product */
$orderItem->setProductId($product->getId())->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType('simple');

/** @var \Magento\Sales\Model\Order $order */
$order->addItem($orderItem);
$order->save();
