<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @var \Magento\Store\Model\Store $store
 */
require __DIR__ . '/../../Store/_files/store.php';

$objectManager = Bootstrap::getObjectManager();

// reindex catalog search to create store's specific temporary tables
$indexerRegistry = $objectManager->create(IndexerRegistry::class);
$indexerRegistry->get(Fulltext::INDEXER_ID)
    ->reindexAll();

/** @var Category $category */
$category = $objectManager->create(Category::class);
$category->setName('category 1')
    ->setUrlKey('cat-1')
    ->setIsActive(true)
    ->setStoreId(1);

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
$categoryRepository->save($category);

/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);

// change default store, otherwise store won't be updated for the category
$storeManager->setCurrentStore($store->getId());
$category->setUrlKey('cat-1-2')
    ->setUrlPath('cat-1-2')
    ->setStoreId($store->getId());

$categoryRepository->save($category);
// back to default store
$storeManager->setCurrentStore(1);

/** @var Product $product */
$product = $objectManager->create(Product::class);
$product->setStoreId(0)
    ->setTypeId(Type::TYPE_SIMPLE)
    ->setName('p002')
    ->setSku('p002')
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
        ]
    )
    ->setQty(100)
    ->setWeight(1);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->save($product);

/** @var CategoryLinkManagementInterface $linkManagement */
$linkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
$linkManagement->assignProductToCategories($product->getSku(), [Category::TREE_ROOT_ID, $category->getEntityId()]);
