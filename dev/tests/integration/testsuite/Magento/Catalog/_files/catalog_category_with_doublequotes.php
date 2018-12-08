<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

/** @var $category \Magento\Catalog\Model\Category */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$categoryFirst = $objectManager->create(\Magento\Catalog\Model\Category::class);
<<<<<<< HEAD
$categoryFirst->isObjectNew(true);
$categoryFirst->setId(523)
    ->setName('Parent Doublequotes Category')
    ->setParentId(2)
    ->setPath('1/2/523')
=======
$categoryFirst->setName('Category 1')
    ->setPath('1/2')
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
