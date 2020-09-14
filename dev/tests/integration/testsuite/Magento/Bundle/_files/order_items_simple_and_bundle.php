<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Bundle/_files/order_item_with_bundle_and_options.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_product.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple333');
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
