<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $category \Magento\Catalog\Model\Category */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

// products from this fixture were moved to indexer_catalog_products.php

$categoryFirst = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryFirst->setName('Category 1')
    ->setPath('1/2')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$categorySecond = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categorySecond->setName('Category 2')
    ->setPath('1/2')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2)
    ->save();

$categoryThird = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryThird->setName('Category 3')
    ->setPath($categoryFirst->getPath())
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2)
    ->save();

$categoryFourth = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryFourth->setName('Category 4')
    ->setPath($categoryThird->getPath())
    ->setLevel(4)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$categoryFifth = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryFifth->setName('Category 5')
    ->setPath($categorySecond->getPath())
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2)
    ->save();
