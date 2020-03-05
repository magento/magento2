<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

$objectManager = Bootstrap::getObjectManager();

/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

/** @var Magento\SalesRule\Model\Rule $rule */
$rule = $registry->registry('_fixture/Magento_SalesRule_Multiple_Categories');

$rule->delete();

// logic to delete the category that was created as part of the rules_category fixture
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter('name', 'Category 1')
    ->create();

/** @var CategoryListInterface $categoryList */
$categoryList = $objectManager->get(CategoryListInterface::class);
$categories = $categoryList->getList($searchCriteria)
    ->getItems();

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);

foreach ($categories as $category) {
    $categoryRepository->delete($category);
}
