<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var $category \Magento\Catalog\Model\Category */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$categoryFirst = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryFirst->setName('Category 1')
    ->setPath('1/2')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

// products from this fixture were moved to indexer_catalog_products.php
$categorySecond = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categorySecond->setName('"Category 6"')
    ->setPath($categoryFirst->getPath())
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();
