<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Registry;
use Magento\Store\Model\Group;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
// Delete product
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
try {
    $product = $productRepository->get('simple333', false, null, true);
    $product->delete();
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    //Product already removed
}
// Delete first category
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('name', 'Category 1')->create();
/** @var CategoryListInterface $categoryList */
$categoryList = $objectManager->get(CategoryListInterface::class);
$categories = $categoryList->getList($searchCriteria)->getItems();
/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
foreach ($categories as $category) {
    $categoryRepository->delete($category);
}
// Delete second category
$searchCriteria = $searchCriteriaBuilder->addFilter('name', 'Category 2')->create();
$categories = $categoryList->getList($searchCriteria)->getItems();
foreach ($categories as $category) {
    $categoryRepository->delete($category);
}
// Delete store group
/** @var Group $store */
$storeGroup = $objectManager->get(Group::class);
$storeGroup->load('test_store_group', 'code');
if ($storeGroup->getId()) {
    $storeGroup->delete();
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
