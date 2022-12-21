<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
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
    ->setPosition(2)
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
    ->setPosition(2)
    ->setDescription('Category 1.1 description.')
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
    ->setPosition(3)
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
    ->setPosition(1)
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
    ->setPosition(1)
    ->save();
