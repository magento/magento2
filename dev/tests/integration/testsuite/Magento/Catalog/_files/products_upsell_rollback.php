<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

use Magento\CatalogInventory\Model\StockRegistryStorage;

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productSkuList = ['simple', 'simple_with_upsell'];
foreach ($productSkuList as $sku) {
    try {
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get($sku, true);
        if ($product->getId()) {
            $productRepository->delete($product);
        }
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        //Product already removed
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

/** @var StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(StockRegistryStorage::class);
$stockRegistryStorage->clean();
