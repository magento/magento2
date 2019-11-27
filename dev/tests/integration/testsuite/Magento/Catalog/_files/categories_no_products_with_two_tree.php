<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;

require __DIR__ . '/categories_no_products.php';

$categoryFactory = $objectManager->create(CategoryFactory::class);
$categoryResource = $objectManager->create(CategoryResource::class);

/** @var $category \Magento\Catalog\Model\Category */
$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setId(13)
    ->setName('Category 2.1')
    ->setParentId(6)
    ->setPath('1/2/6/13')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$categoryResource->save($category);

$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setId(14)
    ->setName('Category 2.2')
    ->setParentId(6)
    ->setPath('1/2/6/14')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2);
$categoryResource->save($category);

$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setId(15)
    ->setName('Category 2.2.1')
    ->setParentId(14)
    ->setPath('1/2/6/14/15')
    ->setLevel(4)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$categoryResource->save($category);
