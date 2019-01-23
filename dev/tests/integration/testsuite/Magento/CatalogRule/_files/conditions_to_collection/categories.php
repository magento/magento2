<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$collection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);

/** @var $category \Magento\Catalog\Model\Category */
$category1 = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category1->isObjectNew(true);
$category1
    ->setName('Category 1')
    ->setParentId(2)
    ->setPath('1/2')
    ->setLevel(2)
    ->setIsActive(true)
    ->setIsAnchor(true)
    ->setPosition(1)
    ->save();

/** @var $category \Magento\Catalog\Model\Category */
$category1_1 = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category1_1->isObjectNew(true);
$category1_1
    ->setName('Category 1.1')
    ->setParentId($category1->getId())
    ->setPath($category1->getPath())
    ->setLevel(3)
    ->setIsActive(true)
    ->setIsAnchor(true)
    ->setPosition(1)
    ->save();

$category1_2 = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category1_2->isObjectNew(true);
$category1_2
    ->setName('Category 1.2')
    ->setParentId($category1->getId())
    ->setPath($category1->getPath())
    ->setLevel(3)
    ->setIsActive(true)
    ->setIsAnchor(true)
    ->setPosition(2)
    ->save();

/** @var $category \Magento\Catalog\Model\Category */
$category1_1_1 = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category1_1_1->isObjectNew(true);
$category1_1_1
    ->setName('Category 1.1.1')
    ->setParentId($category1_1->getId())
    ->setPath($category1_1->getPath())
    ->setLevel(4)
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

/** @var $category \Magento\Catalog\Model\Category */
$category2 = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category2->isObjectNew(true);
$category2
    ->setName('Category 2')
    ->setParentId(2)
    ->setPath('1/2')
    ->setLevel(2)
    ->setIsActive(true)
    ->setPosition(2)
    ->save();

/** @var $category \Magento\Catalog\Model\Category */
$category3 = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category3->isObjectNew(true);
$category3
    ->setName('Category 3')
    ->setParentId(2)
    ->setPath('1/2')
    ->setLevel(2)
    ->setIsActive(true)
    ->setPosition(8)
    ->save();
