<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/attribute.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$storeManager = $objectManager->get(\Magento\Store\Model\StoreManager::class);
$store = $storeManager->getStore('default');
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$installer = $objectManager->get(\Magento\Catalog\Setup\CategorySetup::class);
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');

$product = $objectManager->create(\Magento\Catalog\Model\Product::class)
    ->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Simple Product 1')
    ->setSku('simple1')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ]);
$productRepository->save($product);
$productAction = $objectManager->get(\Magento\Catalog\Model\Product\Action::class);
$productAction->updateAttributes([$product->getId()], ['test_attribute' => 'test_attribute_value'], $store->getId());

$product = $objectManager->create(\Magento\Catalog\Model\Product::class)
    ->setTypeId('simple')
    ->setId(2)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Simple Product 2')
    ->setSku('simple2')
    ->setPrice(9.9)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ]);
$productRepository->save($product);
