<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setName('Simple Related Product')
    ->setSku('simple_related')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->save();

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setName('Simple UpSell Product')
    ->setSku('simple_up')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->save();

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setName('Simple CrossSell Product')
    ->setSku('simple_cross')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->save();

/** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLinkRelated */
$productLinkRelated = $objectManager->create(\Magento\Catalog\Api\Data\ProductLinkInterface::class);
$productLinkRelated->setSku('simple_with_links');
$productLinkRelated->setLinkedProductSku('simple_related');
$productLinkRelated->setPosition(1);
$productLinkRelated->setLinkType('related');

/** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLinkUp */
$productLinkUp = $objectManager->create(\Magento\Catalog\Api\Data\ProductLinkInterface::class);
$productLinkUp->setSku('simple_with_links');
$productLinkUp->setLinkedProductSku('simple_up');
$productLinkUp->setPosition(1);
$productLinkUp->setLinkType('upsell');

/** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLinkCross */
$productLinkCross = $objectManager->create(\Magento\Catalog\Api\Data\ProductLinkInterface::class);
$productLinkCross->setSku('simple_with_links');
$productLinkCross->setLinkedProductSku('simple_cross');
$productLinkCross->setPosition(1);
$productLinkCross->setLinkType('crosssell');

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setName('Simple Product With Links')
    ->setSku('simple_with_links')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['qty' => 100, 'is_in_stock' => 1, 'manage_stock' => 1])
    ->setProductLinks([$productLinkRelated, $productLinkUp, $productLinkCross])
    ->save();
