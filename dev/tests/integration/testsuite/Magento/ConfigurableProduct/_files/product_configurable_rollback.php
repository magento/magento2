<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(10);
if ($product->getId()) {
    $product->delete();
}

/** @var \Magento\CatalogInventory\Model\Stock\Status $stockStatus */
$stockStatus = $objectManager->create('Magento\CatalogInventory\Model\Stock\Status');
$stockStatus->load(10, 'product_id');
$stockStatus->delete();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(20);
if ($product->getId()) {
    $product->delete();
}
/** @var \Magento\CatalogInventory\Model\Stock\Status $stockStatus */
$stockStatus = $objectManager->create('Magento\CatalogInventory\Model\Stock\Status');
$stockStatus->load(20, 'product_id');
$stockStatus->delete();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->load(1);
if ($product->getId()) {
    $product->delete();
}
/** @var \Magento\CatalogInventory\Model\Stock\Status $stockStatus */
$stockStatus = $objectManager->create('Magento\CatalogInventory\Model\Stock\Status');
$stockStatus->load(1, 'product_id');
$stockStatus->delete();

require __DIR__ . '/configurable_attribute_rollback.php';

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
