<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

$productSkus = ['simpleB', 'simpleC'];

foreach ($productSkus as $productSku) {
    try {
        $productRepository->deleteById($productSku);
    } catch (NoSuchEntityException $e) {
        //Already deleted.
    }
}

$categoriesNames = ['Category A', 'Category B', 'Category C'];

foreach ($categoriesNames as $categoryName) {
    try {
        $category = $categoryRepository->get($categoryName);
        $categoryRepository->delete($category);
    } catch (NoSuchEntityException $e) {
        //Already deleted.
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
