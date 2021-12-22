<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Category;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$defaultWebsite = $objectManager->get(StoreManagerInterface::class)->getWebsite();
$groupId = $defaultWebsite->getDefaultGroupId();

// creating english store
/** @var Store $storeEnglish */
$storeEnglish = $objectManager->create(Store::class);
$storeEnglish->setCode('english')
    ->setWebsiteId($defaultWebsite->getId())
    ->setGroupId($groupId)
    ->setName('Fixture For English Store')
    ->setSortOrder(1)
    ->setIsActive(1);
$storeEnglish->save();

// creating ukrainian store
/** @var Store $storeUkrainian */
$storeUkrainian = $objectManager->create(Store::class);
$storeUkrainian->setCode('ukrainian')
    ->setWebsiteId($defaultWebsite->getId())
    ->setGroupId($groupId)
    ->setName('Fixture For Ukrainian Store')
    ->setSortOrder(1)
    ->setIsActive(1);
$storeUkrainian->save();

/** @var Category $categoryEnglish */
$categoryEnglish = $objectManager->create(Category::class);
$categoryEnglish->isObjectNew(true);
$categoryEnglish
    ->setId(33)
    ->setStoreId($storeEnglish->getId())
    ->setName('Category_US')
    ->setParentId(2)
    ->setPath('1/2/33')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(false)
    ->setPosition(1)
    ->setAvailableSortBy(['position']);
$categoryEnglish->save();

/** @var Category $categoryUkrainian */
$categoryUkrainian = $objectManager->create(Category::class);
$categoryUkrainian->isObjectNew(true);
$categoryUkrainian
    ->setId(44)
    ->setStoreId($storeUkrainian->getId())
    ->setName('Category_UA')
    ->setParentId(2)
    ->setPath('1/2/44')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setAvailableSortBy(['position']);
$categoryUkrainian->save();
