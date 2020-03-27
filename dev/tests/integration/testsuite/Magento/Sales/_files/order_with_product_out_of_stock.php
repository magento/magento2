<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Sales/_files/customer_order_item_with_product_and_custom_options.php';

$customerIdFromFixture = 1;
/** @var $order \Magento\Sales\Model\Order */
$order->setCustomerId($customerIdFromFixture)->setCustomerIsGuest(false)->save();

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
