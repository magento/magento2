<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 *
 * Fixture for testing category product count on anchor categories without children.
 * This tests the fix for magento/magento2#40263.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$defaultStoreId = (int) $storeManager->getDefaultStoreView()->getId();

/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

// Create anchor category WITHOUT children (leaf anchor category) using model directly
// This is the scenario that breaks without the fix
/** @var CategoryInterface $leafAnchorCategory */
$leafAnchorCategory = $objectManager->create(CategoryInterface::class);
$leafAnchorCategory->isObjectNew(true);
$leafAnchorCategory
    ->setId(555)
    ->setIsAnchor(true)
    ->setStoreId($defaultStoreId)
    ->setName('Leaf Anchor Category')
    ->setParentId(2)
    ->setPath('1/2/555')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$leafAnchorCategory->save();

// Create a second anchor category without children for bulk testing
/** @var CategoryInterface $leafAnchorCategory2 */
$leafAnchorCategory2 = $objectManager->create(CategoryInterface::class);
$leafAnchorCategory2->isObjectNew(true);
$leafAnchorCategory2
    ->setId(556)
    ->setIsAnchor(true)
    ->setStoreId($defaultStoreId)
    ->setName('Leaf Anchor Category 2')
    ->setParentId(2)
    ->setPath('1/2/556')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2);
$leafAnchorCategory2->save();

// Create product and assign directly to the leaf anchor category
$product = $productFactory->create();
$product
    ->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Product in Leaf Anchor')
    ->setSku('product_in_leaf_anchor')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setCategoryIds([555]);
$productRepository->save($product);

// Create second product for the second leaf category
$product2 = $productFactory->create();
$product2
    ->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Product in Leaf Anchor 2')
    ->setSku('product_in_leaf_anchor_2')
    ->setPrice(20)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setCategoryIds([556]);
$productRepository->save($product2);
