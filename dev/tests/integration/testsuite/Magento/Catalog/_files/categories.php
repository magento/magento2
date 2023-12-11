<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Config;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');
$rootCategoryId = $baseWebsite->getDefaultStore()->getRootCategoryId();

/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);

$defaultAttributeSet = $objectManager->get(Config::class)->getEntityType(Product::ENTITY)->getDefaultAttributeSetId();
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$categoryFactory = $objectManager->get(CategoryInterfaceFactory::class);

$categoryLinkRepository = $objectManager->create(
    CategoryLinkRepositoryInterface::class,
    [
        'productRepository' => $productRepository,
    ]
);

/** @var Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
$reflectionClass = new \ReflectionClass(get_class($categoryLinkManagement));
$properties = [
    'productRepository' => $productRepository,
    'categoryLinkRepository' => $categoryLinkRepository,
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
$category = $categoryFactory->create();
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

$category = $categoryFactory->create();
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
    ->setDescription('Category 1.1 description.')
    ->save();

$category = $categoryFactory->create();
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
    ->setDescription('This is the description for Category 1.1.1')
    ->save();

$category = $categoryFactory->create();
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

$category = $categoryFactory->create();
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

$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setId(8)
    ->setName('Inactive')
    ->setParentId(2)
    ->setPath('1/2/8')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(false)
    ->setPosition(4)
    ->save();

$category = $categoryFactory->create();
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

$category = $categoryFactory->create();
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

$category = $categoryFactory->create();
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

$category = $categoryFactory->create();
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

$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setId(13)
    ->setName('Category 1.2')
    ->setParentId(3)
    ->setPath('1/2/3/13')
    ->setLevel(3)
    ->setDescription('Its a description of Test Category 1.2')
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setIsAnchor(true)
    ->setPosition(2)
    ->save();

/** @var ProductInterfaceFactory $productInterfaceFactory */
$productInterfaceFactory = $objectManager->get(ProductInterfaceFactory::class);

/** @var Product $product */
$product = $productInterfaceFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId($storeManager->getDefaultStoreView()->getId())
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setWeight(18)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);

$simple1 = $productRepository->save($product);

$categoryLinkManagement->assignProductToCategories(
    $simple1->getSku(),
    [2, 3, 4, 13]
);

$product = $productInterfaceFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId($storeManager->getDefaultStoreView()->getId())
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Simple Product Two')
    ->setSku('12345') // SKU intentionally contains digits only
    ->setPrice(45.67)
    ->setWeight(56)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);

$simple2 = $productRepository->save($product);

$categoryLinkManagement->assignProductToCategories(
    $simple2->getSku(),
    [5, 4]
);

$product = $productInterfaceFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId($storeManager->getDefaultStoreView()->getId())
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Simple Product Not Visible On Storefront')
    ->setSku('simple-3')
    ->setPrice(15)
    ->setWeight(2)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
    ->setStatus(Status::STATUS_ENABLED);

$simple3 = $productRepository->save($product);

$categoryLinkManagement->assignProductToCategories(
    $simple3->getSku(),
    [10, 11, 12]
);

$product = $productInterfaceFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($defaultAttributeSet)
    ->setStoreId($storeManager->getDefaultStoreView()->getId())
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Simple Product Three')
    ->setSku('simple-4')
    ->setPrice(10)
    ->setWeight(18)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);

$simple4 = $productRepository->save($product);

$categoryLinkManagement->assignProductToCategories(
    $simple4->getSku(),
    [10, 11, 12, 13]
);
