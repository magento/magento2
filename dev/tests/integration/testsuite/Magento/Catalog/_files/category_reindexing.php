<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/* @var \Magento\Store\Model\StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
$storeManager->setCurrentStore(0);

$defaultAttributeSet = $objectManager->get(Magento\Eav\Model\Config::class)
    ->getEntityType('catalog_product')
    ->getDefaultAttributeSetId();

$productRepository = $objectManager->create(
    \Magento\Catalog\Api\ProductRepositoryInterface::class
);

$categoryLinkRepository = $objectManager->create(
    \Magento\Catalog\Api\CategoryLinkRepositoryInterface::class,
    [
        'productRepository' => $productRepository
    ]
);

/** @var Magento\Catalog\Api\CategoryLinkManagementInterface $linkManagement */
$categoryLinkManagement = $objectManager->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

/**
 * After installation system has two categories: root one with ID:1 and Default category with ID:2
 */
/** @var $category \Magento\Catalog\Model\Category */
$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(3)
    ->setName('Category A')
    ->setParentId(2)
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setIsActive(true)
    ->setIsAnchor(true)
    ->save();

$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(4)
    ->setName('Category B')
    ->setParentId(3)
    ->setPath('1/2/3/4')
    ->setLevel(3)
    ->setIsActive(false)
    ->setIsAnchor(false)
    ->save();

$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(5)
    ->setName('Category C')
    ->setParentId(3)
    ->setPath('1/2/3/4/5')
    ->setLevel(3)
    ->setIsActive(false)
    ->setIsAnchor(false)
    ->save();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setWebsiteIds([1])
    ->setName('Product for category_reindexing test')
    ->setSku('simple')
    ->setPrice(10)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [4, 5]
);
