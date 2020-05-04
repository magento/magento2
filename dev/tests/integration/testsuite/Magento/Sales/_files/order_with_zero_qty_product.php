<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CatalogInventory\Model\Stock;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_with_two_simple_products.php');
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
$customerIdFromFixture = 1;
$order->setCustomerId(1)->setCustomerIsGuest(false)->save();

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
