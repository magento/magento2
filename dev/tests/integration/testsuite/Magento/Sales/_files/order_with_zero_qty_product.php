<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CatalogInventory\Model\Stock;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Sales/_files/order_with_two_simple_products.php';

$customerIdFromFixture = 1;
/** @var $order \Magento\Sales\Model\Order */
$order->setCustomerId($customerIdFromFixture)->setCustomerIsGuest(false)->save();

// load product and set qty to 0
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $repository */
$productRepository = Bootstrap::getObjectManager()->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productSku = 'simple';
/** @var \Magento\Catalog\Model\Product $product */
$product = $productRepository->get($productSku);
// set product qty to zero
$product->setStockData(
    [
        'use_config_manage_stock'   => 1,
        'qty'                       => 0,
        'is_in_stock'               => 1,
        'use_config_backorders' => 0,
        'backorders' => Stock::BACKORDERS_YES_NONOTIFY,
    ]
);
$productRepository->save($product);
