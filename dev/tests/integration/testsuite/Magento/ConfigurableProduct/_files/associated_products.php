<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(3)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 3')
    ->setSku('simple_3')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setQty(22);
$product = $productRepository->save($product);

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(14)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 14')
    ->setSku('simple_14')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setQty(22);
$product = $productRepository->save($product);

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(15)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 15')
    ->setSku('simple_15')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setQty(22);
$product = $productRepository->save($product);

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId('simple')
    ->setId(92)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 92')
    ->setSku('simple_92')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setQty(22);
$product = $productRepository->save($product);
