<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Store/_files/second_store.php';
Bootstrap::getInstance()
    ->loadArea(FrontNameResolver::AREA_CODE);

/**
 * After installation system has categories:
 *
 * root one with ID:1 and Default category with ID:3 both with StoreId:1,
 *
 * root one with ID:1 and Default category with ID:2 both with StoreId:2
 */

$store = Bootstrap::getObjectManager()->get(Store::class);
$store->load('fixture_second_store', 'code');

/** @var $category Category */
$category = Bootstrap::getObjectManager()->create(Category::class);
$category->isObjectNew(true);
$category->setId(3)
    ->setName('Category 1')
    ->setParentId(1)
    ->setPath('1/2')
    ->setLevel(1)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$category = Bootstrap::getObjectManager()->create(Category::class);
$category->isObjectNew(true);
$category->setId(4)
    ->setName('Category 1.1')
    ->setParentId(3)
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$category = Bootstrap::getObjectManager()->create(Category::class);
$category->isObjectNew(true);
$category->setId(3)
    ->setName('Category 1')
    ->setParentId(2)
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setStoreId($store->getId())
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$category = Bootstrap::getObjectManager()->create(Category::class);
$category->isObjectNew(true);
$category->setId(4)
    ->setName('Category 1.1')
    ->setParentId(3)
    ->setPath('1/2/3/4')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setStoreId($store->getId())
    ->setIsActive(true)
    ->setPosition(1)
    ->save();
