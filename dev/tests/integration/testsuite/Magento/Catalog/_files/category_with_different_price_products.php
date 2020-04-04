<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Helper\DefaultCategory;

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var CategoryInterfaceFactory $categoryFactory */
$categoryFactory = $objectManager->get(CategoryInterfaceFactory::class);
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
/** @var DefaultCategory $categoryHelper */
$categoryHelper = $objectManager->get(DefaultCategory::class);
$currentStoreId = $storeManager->getStore()->getId();
$defaultWebsiteId = $storeManager->getWebsite('base')->getId();

$storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
$category = $categoryFactory->create();
$category->isObjectNew(true);
$category->setName('Category 999')
    ->setParentId($categoryHelper->getId())
    ->setLevel(2)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1);
$category = $categoryRepository->save($category);
$storeManager->setCurrentStore($currentStoreId);

$product = $productFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setStoreId(Store::DEFAULT_STORE_ID)
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Simple Product With Price 10')
    ->setSku('simple1000')
    ->setPrice(10)
    ->setWeight(1)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setCategoryIds([$category->getId()])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);
$productRepository->save($product);

$product2 = $productFactory->create();
$product2->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product2->getDefaultAttributeSetId())
    ->setStoreId(Store::DEFAULT_STORE_ID)
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Simple Product With Price 20')
    ->setSku('simple1001')
    ->setPrice(20)
    ->setWeight(1)
    ->setStockData(['use_config_manage_stock' => 0])
    ->setCategoryIds([$category->getId()])
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);
$productRepository->save($product2);
