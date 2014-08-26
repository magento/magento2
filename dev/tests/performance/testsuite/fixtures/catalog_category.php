<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var \Magento\TestFramework\Application $this */

$installer = $this->getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Setup',
    array('resourceName' => 'catalog_setup')
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
    array(1)
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
    array(2, 3)
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
)->setStockId(
    \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID
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
