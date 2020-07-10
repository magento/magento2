<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Product $product */
$product = $objectManager->create(Product::class);
$product->load(6);
/** @var StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = $objectManager->get(StockRegistryStorage::class);

if ($product->getId()) {
    $product->delete();
}

$stockRegistryStorage->clean();
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
