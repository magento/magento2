<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/store_with_second_root_category.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_with_category.php');

$objectManager = Bootstrap::getObjectManager();
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
$categoryCollectionFactory = $objectManager->get(CollectionFactory::class);
$categoryFactory = $objectManager->get(CategoryFactory::class);

$defaultWebsiteId = $websiteRepository->get('base')->getId();
$secondWebsiteId = $websiteRepository->get('test')->getId();

/** @var $categoryCollection Collection */
$categoryCollection = $categoryCollectionFactory->create();
$categoryCollection->addFieldToFilter('name', ['eq' => 'Second Root Category']);
$secondRootCategory = $categoryCollection->getFirstItem();

$subCategory = $categoryFactory->create();
$subCategory
    ->setName('Second Root Subcategory')
    ->setParentId($secondRootCategory->getEntityId())
    ->setLevel(2)
    ->setAvailableSortBy(['position', 'name'])
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$subCategory = $categoryRepository->save($subCategory);

$subSubCategory = $categoryFactory->create();
$subSubCategory
    ->setName('Second Root Subsubcategory')
    ->setParentId($subCategory->getEntityId())
    ->setLevel(2)
    ->setAvailableSortBy(['position', 'name'])
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$subSubCategory = $categoryRepository->save($subSubCategory);

/** @var $product ProductInterface */
$product = $productRepository->get('in-stock-product');
$product
    ->setUrlKey('in-stock-product')
    ->setWebsiteIds([$defaultWebsiteId, $secondWebsiteId])
    ->setCategoryIds(
        [2, 333, $secondRootCategory->getEntityId(), $subCategory->getEntityId(), $subSubCategory->getEntityId()]
    );
$productRepository->save($product);
