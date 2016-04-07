<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Catalog\Setup\CategorySetup $installer */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Setup\CategorySetup');
/**
 * After installation system has two categories: root one with ID:1 and Default category with ID:2
 */
/** @var $category \Magento\Catalog\Model\Category */
$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(
    9
)->setName(
    'Category 9'
)->setParentId(
    2
)->setPath(
    '1/2/3'
)->setLevel(
    2
)->setAvailableSortBy(
    'name'
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->save();

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setId(
    1
)->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setAttributeSetId(
    $installer->getAttributeSetId('catalog_product', 'Default')
)->setStoreId(
    1
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product One'
)->setSku(
    'simple'
)->setPrice(
    10
)->setWeight(
    18
)->setStockData(
    [
        'use_config_manage_stock'   => 1,
        'qty'                       => 100,
        'is_qty_decimal'            => 0,
        'is_in_stock'               => 1,
    ]
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->save();
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create('Magento\Catalog\Api\CategoryLinkManagementInterface');
$categoryLinkManagement->assignProductToCategories('simple', [9]);
