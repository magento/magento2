<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $category \Magento\Catalog\Model\Category */
$category1 = $objectManager->create('Magento\Catalog\Model\Category');
$category1->isObjectNew(true);
$category1->setName('Category 1')
    ->setParentId(2)
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();
$category1->setPath('1/2/' . $category1->getId())->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setCategoryIds([$category1->getId()])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();

$category2 = $objectManager->create('Magento\Catalog\Model\Category');
$category2->isObjectNew(true);
$category2->setName('Category 2')
    ->setParentId(2)
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2)
    ->save();
$category2->setPath('1/2/' . $category2->getId())->save();

$category3 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Category');
$category3->isObjectNew(true);
$category3->setName('Old Root')
    ->setParentId(1)
    ->setLevel(1)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(3)
    ->save();
$category3->setPath('1/' . $category3->getId())->save();
