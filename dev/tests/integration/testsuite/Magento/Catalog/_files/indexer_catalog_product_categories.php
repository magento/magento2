<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $category \Magento\Catalog\Model\Category */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\Category $categoryFirst */
$categoryFirst = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryFirst->setName('Category 1')
    ->setPath('1/2')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setDefaultSortBy('name')
    ->setIsAnchor(true)
    ->save();

/** @var \Magento\Catalog\Model\Category $categorySecond */
$categorySecond = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categorySecond->setName('Category 2')
    ->setPath($categoryFirst->getPath())
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setDefaultSortBy('name')
    ->setIsAnchor(false)
    ->save();

/** @var \Magento\Catalog\Model\Category $categoryThird */
$categoryThird = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryThird->setName('Category 3')
    ->setPath($categorySecond->getPath())
    ->setLevel(4)
    ->setAvailableSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setDefaultSortBy('name')
    ->setIsAnchor(true)
    ->save();

/** @var \Magento\Catalog\Model\Category $categoryFourth */
$categoryFourth = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryFourth->setName('Category 4')
    ->setPath($categoryFirst->getPath())
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setIsActive(true)
    ->setPosition(2)
    ->setDefaultSortBy('name')
    ->setIsAnchor(true)
    ->save();
