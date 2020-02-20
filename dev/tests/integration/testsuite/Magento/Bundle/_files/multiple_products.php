<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Msrp\Model\Product\Attribute\Source\Type;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$defaultWebsiteId = $websiteRepository->get('base')->getId();
$product = $productFactory->create();
$product->setTypeId(ProductType::TYPE_SIMPLE)
    ->setId(10)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setName('Simple Product')
    ->setSku('simple1')
    ->setTaxClassId(0)
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(Type::TYPE_IN_CART)
    ->setPrice(10)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([$defaultWebsiteId])
    ->setCategoryIds([])
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
        'manage_stock' => 1,
    ]);
$productRepository->save($product);

$product2 = $productFactory->create();
$product2->setTypeId(ProductType::TYPE_SIMPLE)
    ->setId(11)
    ->setAttributeSetId($product2->getDefaultAttributeSetId())
    ->setName('Simple Product2')
    ->setSku('simple2')
    ->setTaxClassId(0)
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(Type::TYPE_ON_GESTURE)
    ->setPrice(20)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_IN_CATALOG)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([$defaultWebsiteId])
    ->setCategoryIds([])
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 50,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
        'manage_stock' => 1,
    ]);
$productRepository->save($product2);

$product3 = $productFactory->create();
$product3->setTypeId(ProductType::TYPE_SIMPLE)
    ->setId(12)
    ->setAttributeSetId($product3->getDefaultAttributeSetId())
    ->setName('Simple Product 3')
    ->setSku('simple3')
    ->setTaxClassId(0)
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setPrice(30)
    ->setWeight(1)
    ->setVisibility(Visibility::VISIBILITY_IN_CATALOG)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([$defaultWebsiteId])
    ->setCategoryIds([])
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 140,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
        'manage_stock' => 1,
    ]);
$productRepository->save($product3);

$product4 = $productFactory->create();
$product4->setTypeId(ProductType::TYPE_SIMPLE)
    ->setId(13)
    ->setAttributeSetId($product4->getDefaultAttributeSetId())
    ->setName('Simple Product 4')
    ->setSku('simple4')
    ->setTaxClassId(0)
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(Type::TYPE_IN_CART)
    ->setPrice(13)
    ->setWeight(12)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([$defaultWebsiteId])
    ->setCategoryIds([])
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 20,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
        'manage_stock' => 1,
    ]);
$productRepository->save($product4);

$product5 = $productFactory->create();
$product5->setTypeId(ProductType::TYPE_SIMPLE)
    ->setId(14)
    ->setAttributeSetId($product5->getDefaultAttributeSetId())
    ->setName('Simple Product 5')
    ->setSku('simple5')
    ->setTaxClassId(0)
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(Type::TYPE_IN_CART)
    ->setPrice(14)
    ->setWeight(10)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([$defaultWebsiteId])
    ->setCategoryIds([])
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 15,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
        'manage_stock' => 1,
    ]);
$productRepository->save($product5);
