<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$defaultCategory = $objectManager->create(\Magento\Catalog\Helper\DefaultCategory::class);
/** @var \Magento\Catalog\Model\Category $category */
$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setCreatedAt('2014-06-23 09:50:07')
    ->setName('Category 1')
    ->setParentId($defaultCategory->getId())
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setAvailableSortBy(['position'])
    ->save();

/** @var \Magento\Store\Model\Store $store */
$store = $objectManager->create(\Magento\Store\Model\Store::class);

$category->setStoreId($store->load('default')->getId())
    ->setName('category-default-store')
    ->setUrlKey('category-default-store')
    ->save();

$rootCategoryForTestStoreGroup = $objectManager->create(\Magento\Catalog\Model\Category::class);
$rootCategoryForTestStoreGroup->isObjectNew(true);
$rootCategoryForTestStoreGroup->setCreatedAt('2014-06-23 09:50:07')
    ->setName('Category 2')
    ->setParentId(\Magento\Catalog\Model\Category::TREE_ROOT_ID)
    ->setPath('1/2/334')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setAvailableSortBy(['position'])
    ->save();

$rootCategoryForTestStoreGroup->setStoreId($store->load('test')->getId())
    ->setName('category-test-store')
    ->setUrlKey('category-test-store')
    ->save();

$storeCode = 'test';
/** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
/** @var \Magento\Store\Api\Data\StoreInterface $store */
$store = $storeRepository->get($storeCode);

/** @var \Magento\Store\Model\Group $storeGroup */
$storeGroup = $objectManager->create(\Magento\Store\Model\Group::class)
    ->setWebsiteId('1')
    ->setCode('test_store_group')
    ->setName('Test Store Group')
    ->setRootCategoryId($rootCategoryForTestStoreGroup->getId())
    ->setDefaultStoreId($store->getId())
    ->save();

$store->setGroupId($storeGroup->getId())->save();
