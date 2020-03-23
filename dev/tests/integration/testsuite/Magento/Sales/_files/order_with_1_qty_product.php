<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Sales/_files/order_with_two_simple_products_qty_10.php';

$customerIdFromFixture = 1;
/** @var $order \Magento\Sales\Model\Order */
$order->setCustomerId($customerIdFromFixture)->setCustomerIsGuest(false)->save();

// load product and set qty to 1
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $repository */
$productRepository = Bootstrap::getObjectManager()->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productSku = 'simple';
/** @var \Magento\Catalog\Model\Product $product */
$product = $productRepository->get($productSku);
// set product qty to 1
$product->setStockData(
    [
        'use_config_manage_stock'   => 1,
        'qty'                       => 1,
    ]
);
$productRepository->save($product);
