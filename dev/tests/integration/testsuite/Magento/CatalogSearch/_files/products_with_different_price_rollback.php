<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
foreach (['simple_11', 'simple_12', 'simple_23', 'simple_23', 'simple_100'] as $sku) {
    try {
        $product = $productRepository->get($sku, false, null, true);
        $productRepository->delete($product);
    } catch (NoSuchEntityException $e) {
        //Product already removed
    }
}

$categoryRepository = Bootstrap::getObjectManager()->get(CategoryRepositoryInterface::class);
try {
    $category = $categoryRepository->get(4);
    $categoryRepository->delete($category);
} catch (NoSuchEntityException $e) {
    //Category already removed
}
