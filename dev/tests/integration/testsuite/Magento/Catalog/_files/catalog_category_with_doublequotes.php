<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/** @var $category \Magento\Catalog\Model\Category */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
try {
    $category = $categoryRepository->get(333);
} catch (NoSuchEntityException $e) {
    require_once __DIR__ . '/category.php';
}

// products from this fixture were moved to indexer_catalog_products.php
$categorySecond = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categorySecond->setName('"Category 6"')
    ->setPath($category->getPath())
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();
