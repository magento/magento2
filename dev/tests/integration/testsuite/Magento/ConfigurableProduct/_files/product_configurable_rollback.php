<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Exception\NoSuchEntityException;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Catalog\Api\ProductRepositoryInterface');
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->loadByAttribute('sku', 'simple_10');
$firstProductEntityId = $product->getEntityId();
if ($product->getId()) {
    $product->delete();
}

/** @var \Magento\CatalogInventory\Model\Stock\Status $stockStatus */
$stockStatus = $objectManager->create('Magento\CatalogInventory\Model\Stock\Status');
$stockStatus->load($firstProductEntityId, 'product_id');
$stockStatus->delete();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->loadByAttribute('sku', 'simple_20');
$secondProductEntityId = $product->getEntityId();
if ($product->getId()) {
    $product->delete();
}
/** @var \Magento\CatalogInventory\Model\Stock\Status $stockStatus */
$stockStatus = $objectManager->create('Magento\CatalogInventory\Model\Stock\Status');
$stockStatus->load($secondProductEntityId, 'product_id');
$stockStatus->delete();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->loadByAttribute('sku', 'configurable');
$entityId = $product->getEntityId();
if ($entityId) {
    $product->delete();
}
/** @var \Magento\CatalogInventory\Model\Stock\Status $stockStatus */
$stockStatus = $objectManager->create('Magento\CatalogInventory\Model\Stock\Status');
$stockStatus->load($entityId, 'product_id');
$stockStatus->delete();

require __DIR__ . '/configurable_attribute_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
