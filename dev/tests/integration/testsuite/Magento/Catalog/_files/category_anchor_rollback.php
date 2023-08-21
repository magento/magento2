<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CategoryRepository $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepository::class);

$categoryIds = [11, 22];
foreach ($categoryIds as $categoryId) {
    /** @var CategoryInterface $category */
    $category = $categoryRepository->get($categoryId);
    if ($category->getId()) {
        $categoryRepository->delete($category);
    }
}

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter(ProductInterface::SKU, 'product_anchor_%', 'like');

/** @var ProductSearchResultsInterface $products */
$products = $productRepository->getList($searchCriteriaBuilder->create());
/** @var ProductInterface $product */
foreach ($products->getItems() as $product) {
    $productRepository->delete($product);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
