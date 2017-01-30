<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$defaultAttributeSet = $objectManager->get(Magento\Eav\Model\Config::class)
    ->getEntityType('catalog_product')
    ->getDefaultAttributeSetId();

$productRepository = $objectManager->create(
    'Magento\Catalog\Api\ProductRepositoryInterface'
);

$categoryLinkRepository = $objectManager->create(
    'Magento\Catalog\Api\CategoryLinkRepositoryInterface',
    [
        'productRepository' => $productRepository
    ]
);

/** @var Magento\Catalog\Api\CategoryLinkManagementInterface $linkManagement */
$categoryLinkManagement = $objectManager->create('Magento\Catalog\Api\CategoryLinkManagementInterface');
$reflectionClass = new \ReflectionClass(get_class($categoryLinkManagement));
$properties = [
    'productRepository' => $productRepository,
    'categoryLinkRepository' => $categoryLinkRepository
];
foreach ($properties as $key => $value) {
    if ($reflectionClass->hasProperty($key)) {
        $reflectionProperty = $reflectionClass->getProperty($key);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($categoryLinkManagement, $value);
    }
}


/**
 * After installation system has two categories: root one with ID:1 and Default category with ID:2
 */
/** @var $category \Magento\Catalog\Model\Category */
$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(3)
    ->setName('Category 1')
    ->setParentId(2)
    ->setPath('1/2/3')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(4)
    ->setName('Category 1.1')
    ->setParentId(3)
    ->setPath('1/2/3/4')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setIsAnchor(true)
    ->setPosition(1)
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(5)
    ->setName('Category 1.1.1')
    ->setParentId(4)
    ->setPath('1/2/3/4/5')
    ->setLevel(4)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setCustomUseParentSettings(0)
    ->setCustomDesign('Magento/blank')
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(6)
    ->setName('Category 2')
    ->setParentId(2)
    ->setPath('1/2/6')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(2)
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(7)
    ->setName('Movable')
    ->setParentId(2)
    ->setPath('1/2/7')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(3)
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(8)
    ->setName('Inactive')
    ->setParentId(2)
    ->setPath('1/2/8')
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(false)
    ->setPosition(4)
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(9)
    ->setName('Movable Position 1')
    ->setParentId(2)
    ->setPath('1/2/9')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(5)
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(10)
    ->setName('Movable Position 2')
    ->setParentId(2)
    ->setPath('1/2/10')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(6)
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(11)
    ->setName('Movable Position 3')
    ->setParentId(2)
    ->setPath('1/2/11')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(7)
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(12)
    ->setName('Category 12')
    ->setParentId(2)
    ->setPath('1/2/12')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(8)
    ->save();

$category = $objectManager->create('Magento\Catalog\Model\Category');
$category->isObjectNew(true);
$category->setId(13)
    ->setName('Category 1.2')
    ->setParentId(3)
    ->setPath('1/2/3/13')
    ->setLevel(3)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setIsAnchor(true)
    ->setPosition(2)
    ->save();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId(1)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setWeight(18)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2, 3, 4, 13]
);

$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId(1)
    ->setWebsiteIds([1])
    ->setName('Simple Product Two')
    ->setSku('12345') // SKU intentionally contains digits only
    ->setPrice(45.67)
    ->setWeight(56)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [5, 4]
);

$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId(1)
    ->setWebsiteIds([1])
    ->setName('Simple Product Not Visible On Storefront')
    ->setSku('simple-3')
    ->setPrice(15)
    ->setWeight(2)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [10, 11, 12]
);

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId(1)
    ->setWebsiteIds([1])
    ->setName('Simple Product Three')
    ->setSku('simple-4')
    ->setPrice(10)
    ->setWeight(18)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [10, 11, 12, 13]
);
