<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CatalogInventory\Model\StockRegistryStorage;

/** @var \Magento\Framework\Registry $registry */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
$collection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
$collection->addAttributeToSelect('id')->load();
if ($collection->count() > 0) {
    $collection->delete();
}

/** @var StockRegistryStorage $stockRegistryStorage */
$stockRegistryStorage = $objectManager->get(StockRegistryStorage::class);
$stockRegistryStorage->removeStockItem(1);
$stockRegistryStorage->removeStockItem(2);
$stockRegistryStorage->removeStockItem(3);
$stockRegistryStorage->removeStockItem(4);
$stockRegistryStorage->removeStockItem(5);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
