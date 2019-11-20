<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(100)
    ->setWeight(1)
    ->setTierPrice([0 => ['website_id' => 0, 'cust_group' => 1, 'price_qty' => 5, 'price' => 95]])
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setCanSaveCustomOptions(true)
    ->setStockData(
        [
            'qty' => 10,
            'is_in_stock' => 1,
            'manage_stock' => 1,
        ]
    );
$productRepository->save($product);
$product->unsetData()->setOrigData();

$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Second Simple Product')
    ->setSku('second_simple')
    ->setPrice(200)
    ->setWeight(1)
    ->setTierPrice(
        [
            0 => [
                'website_id' => 0,
                'cust_group' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'price_qty' => 10,
                'price' => 3,
                'percentage_value' => 3,
            ],
        ]
    )
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setCanSaveCustomOptions(true)
    ->setStockData(
        [
            'qty' => 10,
            'is_in_stock' => 1,
            'manage_stock' => 1,
        ]
    );

$productRepository->save($product);
