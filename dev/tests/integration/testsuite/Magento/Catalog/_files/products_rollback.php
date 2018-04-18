<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/**
 * @var Magento\Catalog\Api\ProductRepositoryInterface $productRepository
 */
$productRepository = Bootstrap::getObjectManager()
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

// Remove product stock registry data.
/** @var \Magento\CatalogInventory\Model\StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = Bootstrap::getObjectManager()->get(
    \Magento\CatalogInventory\Model\StockRegistryStorage::class
);
$stockRegistryStorage->removeStockItem(1);
$stockRegistryStorage->removeStockStatus(1);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
