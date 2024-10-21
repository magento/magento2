<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store.php');

$objectManager = Bootstrap::getObjectManager();

/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->get(StoreRepositoryInterface::class);
$secondStore = $storeRepository->get('fixture_second_store');

/** @var CategoryResource $categoryResource */
$categoryResource = $objectManager->get(CategoryResource::class);

/** @var ProductResource $productResource */
$productResource = $objectManager->get(ProductResource::class);

/** @var CategoryInterface|Category $category */
$category = $objectManager->create(CategoryInterface::class);
$category->isObjectNew(true);
$category
    ->setId(113)
    ->setIsAnchor(true)
    ->setStoreId(Store::DEFAULT_STORE_ID)
    ->setName('Test Category In Default Store')
    ->setDescription('Test description in default store')
    ->setParentId(2)
    ->setPath('1/2/113')
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true);
$categoryResource->save($category);

$category
    ->setStoreId($secondStore->getId())
    ->setName('Test Category In Second Store')
    ->setDescription('Test description in second store');
$categoryResource->save($category);

/** @var Product $product  */
$product = $objectManager->create(Product::class);
$product
    ->setTypeId('simple')
    ->setId(116)
    ->setAttributeSetId(4)
    ->setWebsiteIds([$secondStore->getWebsiteId()])
    ->setName('Simple Product116')
    ->setSku('simple116')
    ->setPrice(10)
    ->setMetaTitle('meta title2')
    ->setMetaKeyword('meta keyword2')
    ->setMetaDescription('meta description2')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 0]);
$productResource->save($product);
