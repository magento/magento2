<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Category;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$defaultWebsite = $storeManager->getWebsite();
$defaultStoreId = $storeManager->getStore()->getId();
$groupId = $defaultWebsite->getDefaultGroupId();

/** @var Category $category */
$category = $objectManager->create(Category::class);
$category->isObjectNew(true);
$category
    ->setId(10)
    ->setStoreId($defaultStoreId)
    ->setIncludeInMenu(false)
    ->setName('Category_en')
    ->setDescription('Category_en Description')
    ->setDisplayMode(Category::DM_MIXED)
    ->setAvailableSortBy(['name', 'price'])
    ->setDefaultSortBy('price')
    ->setUrlKey('category-en')
    ->setMetaTitle('Category_en Meta Title')
    ->setMetaKeywords('Category_en Meta Keywords')
    ->setMetaDescription('Category_en Meta Description')
    ->setParentId(2)
    ->setPath('1/2/10')
    ->setLevel(2)
    ->setIsActive(true)
    ->setPosition(1);
$category->save();
