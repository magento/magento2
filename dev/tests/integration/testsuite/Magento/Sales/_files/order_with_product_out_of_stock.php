<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Sales/_files/customer_order_item_with_product_and_custom_options.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$order->setCustomerId(1)->setCustomerIsGuest(false)->save();

// load product and set it out of stock
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $repository */
$productRepository = Bootstrap::getObjectManager()->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productSku = 'simple';
/** @var \Magento\Catalog\Model\Product $product */
$product = $productRepository->get($productSku);
// set product as out of stock
$product->setStockData(
    [
        'use_config_manage_stock'   => 1,
        'qty'                       => 0,
        'is_qty_decimal'            => 0,
        'is_in_stock'               => 0,
    ]
);
$productRepository->save($product);
