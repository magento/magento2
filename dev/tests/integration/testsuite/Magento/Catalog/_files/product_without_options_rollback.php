<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$repository = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
try {
    $product = $repository->get('simple', false, null, true);
    $product->delete();
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    //Entity already deleted
}

// Remove product stock registry data.
/* @var \Magento\CatalogInventory\Model\StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = $objectManager->get(\Magento\CatalogInventory\Model\StockRegistryStorage::class);
$stockRegistryStorage->removeStockItem(1);
$stockRegistryStorage->removeStockStatus(1);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
