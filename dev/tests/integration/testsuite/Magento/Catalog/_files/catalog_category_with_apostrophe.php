<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $category \Magento\Catalog\Model\Category */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$categoryFirst = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryFirst->isObjectNew(true);
$categoryFirst->setId(523)
    ->setName('Parent Apostrophe Category')
    ->setParentId(2)
    ->setPath('1/2/523')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

// products from this fixture were moved to indexer_catalog_products.php
$categorySecond = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categorySecond->setName('\'Category 6\'')
    ->setPath($categoryFirst->getPath())
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();
