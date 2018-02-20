<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CatalogInventory\Model\StockRegistryStorage;

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/**
 * @var Magento\Catalog\Api\ProductRepositoryInterface $productRepository
 */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
try {
    $product = $productRepository->get('simple', false, null, true);
    $productRepository->delete($product);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    //Product already removed
}

try {
    $customDesignProduct = $productRepository->get('custom-design-simple-product', false, null, true);
    $productRepository->delete($customDesignProduct);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    //Product already removed
}

/** @var StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(StockRegistryStorage::class);
$stockRegistryStorage->removeStockItem(1);
$stockRegistryStorage->removeStockItem(2);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
