<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

// Delete products
$productSkus = ['product_in_leaf_anchor', 'product_in_leaf_anchor_2'];
foreach ($productSkus as $sku) {
    try {
        $product = $productRepository->get($sku);
        $productRepository->delete($product);
    } catch (NoSuchEntityException $e) {
        // Product already deleted
    }
}

// Delete categories
$categoryIds = [555, 556];
foreach ($categoryIds as $categoryId) {
    try {
        $category = $categoryRepository->get($categoryId);
        $categoryRepository->delete($category);
    } catch (NoSuchEntityException $e) {
        // Category already deleted
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
