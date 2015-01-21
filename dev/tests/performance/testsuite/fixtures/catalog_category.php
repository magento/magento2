<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\TestFramework\Application $this */

$installer = $this->getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Setup',
    ['resourceName' => 'catalog_setup']
);
/**
 * After installation system has two categories: root one with ID:1 and Default category with ID:2
 */
$category = $this->getObjectManager()->create('Magento\Catalog\Model\Category');

$category->setId(
    3
)->setName(
    'Category 1'
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

$product = $this->getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setAttributeSetId(
    $installer->getAttributeSetId('catalog_product', 'Default')
)->setStoreId(
    1
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product'
)->setDescription(
    'Description'
)->setShortDescription(
    'Desc'
)->setSku(
    'simple'
)->setPrice(
    10
)->setWeight(
    18
)->setCategoryIds(
    [2, 3]
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setTaxClassId(
    2
)->save();

$stockItem = $this->getObjectManager()->create('Magento\CatalogInventory\Model\Stock\Item');
$stockItem->setProductId(
    $product->getId()
)->setTypeId(
    $product->getTypeId()
)->setIsInStock(
    1
)->setQty(
    10000
)->setUseConfigMinQty(
    1
)->setUseConfigBackorders(
    1
)->setUseConfigMinSaleQty(
    1
)->setUseConfigMaxSaleQty(
    1
)->setUseConfigNotifyStockQty(
    1
)->setUseConfigManageStock(
    1
)->setUseConfigQtyIncrements(
    1
)->setUseConfigEnableQtyInc(
    1
)->save();
