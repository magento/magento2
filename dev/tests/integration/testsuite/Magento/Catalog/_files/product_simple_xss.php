<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$objectManager->removeSharedInstance(\Magento\Catalog\Model\ProductRepository::class);
$objectManager->removeSharedInstance(\Magento\Catalog\Model\CategoryLinkRepository::class);

/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('<script>alert("xss");</script>')
    ->setSku('product-with-xss')
    ->setPrice(10)
    ->setDescription('Description with <b>html tag</b>')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2]
);
