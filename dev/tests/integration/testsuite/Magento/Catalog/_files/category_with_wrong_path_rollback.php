<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryList;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
/** @var CategoryRepository $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepository::class);
/** @var CategoryList $categoryList */
$categoryList = $objectManager->get(CategoryList::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$searchCriteria = $searchCriteriaBuilder
    ->addFilter(CategoryInterface::KEY_NAME, 'Category With Wrong Path')
    ->create();
$categories = $categoryList->getList($searchCriteria)->getItems();

foreach ($categories as $category) {
    $categoryRepository->delete($category);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
