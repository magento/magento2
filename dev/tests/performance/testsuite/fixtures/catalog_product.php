<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\TestFramework\Application $this */

// Extract product set id
$productResource = $this->getObjectManager()->create('Magento\Catalog\Model\Product');
$entityType = $productResource->getResource()->getEntityType();
$sets = $this->getObjectManager()->create(
    'Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection'
)->setEntityTypeFilter(
    $entityType->getId()
)->load();

$setId = null;
foreach ($sets as $setInfo) {
    $setId = $setInfo->getId();
    break;
}
if (!$setId) {
    throw new \Exception('No attributes sets for product found.');
}

// Create product
$product = $this->getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    'simple'
)->setAttributeSetId(
    $setId
)->setWebsiteIds(
    [1]
)->setName(
    'Product 1'
)->setShortDescription(
    'Product 1 Short Description'
)->setWeight(
    1
)->setDescription(
    'Product 1 Description'
)->setSku(
    'product_1'
)->setPrice(
    10
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
