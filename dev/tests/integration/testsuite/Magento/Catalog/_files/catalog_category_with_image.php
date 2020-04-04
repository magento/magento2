<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'catalog_category_image.php';

/** @var $category \Magento\Catalog\Model\Category */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$categoryParent = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryParent->setName('Parent Image Category')
    ->setPath('1/2')
    ->setLevel(2)
    ->setImage($filePath)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$categoryChild = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryChild->setName('Child Image Category')
    ->setPath($categoryParent->getPath())
    ->setLevel(3)
    ->setImage($filePath)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2)
    ->save();
