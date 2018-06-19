<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\Category $category */
$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(333)
    ->setCreatedAt('2014-06-23 09:50:07')
    ->setName('Category 1')
    ->setParentId(2)
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setAvailableSortBy(['position'])
    ->save();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(333)
    ->setAttributeSetId(4)
    ->setStoreId(1)
    ->setWebsiteIds([1])
    ->setName('Simple Product Three')
        ->setSku('simple333')
    ->setPrice(10)
    ->setWeight(18)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setCategoryIds([333])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();

/** @var \Magento\Store\Model\Store $store */
$store = $objectManager->create(\Magento\Store\Model\Store::class);

$category->setStoreId($store->load('default')->getId())
    ->setName('category-default-store')
    ->setUrlKey('category-default-store')
    ->save();

$rootCategoryForTestStoreGroup = $objectManager->create(\Magento\Catalog\Model\Category::class);
$rootCategoryForTestStoreGroup->isObjectNew(true);
$rootCategoryForTestStoreGroup->setId(334)
    ->setCreatedAt('2014-06-23 09:50:07')
    ->setName('Category 2')
    ->setParentId(1)
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
/** @var \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->create(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
/** @var \Magento\Catalog\Api\Data\CategoryInterface $category */
$category = $categoryRepository->get(334);
/** @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->create(\Magento\Store\Api\StoreRepositoryInterface::class);
/** @var \Magento\Store\Api\Data\StoreInterface $store */
$store = $storeRepository->get($storeCode);

/** @var \Magento\Store\Model\Group $storeGroup */
$storeGroup = $objectManager->create(\Magento\Store\Model\Group::class);
$storeGroup->setWebsiteId('1');
$storeGroup->setCode('test_store_group');
$storeGroup->setName('Test Store Group');
$storeGroup->setRootCategoryId($category->getId());
$storeGroup->setDefaultStoreId($store->getId());
$storeGroup->save();

$store->setGroupId($storeGroup->getId())->save();

/* Refresh stores memory cache */
$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
