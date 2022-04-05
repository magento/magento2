<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\Collection;
use Magento\Msrp\Model\Product\Attribute\Source\Type as SourceType;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/layered_navigation_attribute.php');

$objectManager = Bootstrap::getObjectManager();

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductInterfaceFactory $productInterfaceFactory */
$productInterfaceFactory = $objectManager->get(ProductInterfaceFactory::class);

/** @var Product $product */
$product = $productInterfaceFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setName('Simple Product1')
    ->setSku('simple1')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(SourceType::TYPE_IN_CART)
    ->setPrice(10)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setCategoryIds([])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setSpecialPrice('5.99');
$simple1 = $productRepository->save($product);

/** @var Product $product */
$product = $productInterfaceFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setName('Simple Product2')
    ->setSku('simple2')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(SourceType::TYPE_ON_GESTURE)
    ->setPrice(20)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setCategoryIds([])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 50, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setSpecialPrice('15.99');
$simple2 = $productRepository->save($product);

/** @var Product $product */
$product = $productInterfaceFactory->create();
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setName('Simple Product3')
    ->setSku('simple3')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setPrice(30)
    ->setWeight(1)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_DISABLED)
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setCategoryIds([])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 140, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setSpecialPrice('25.99');
$simple3 = $productRepository->save($product);

/** @var CategoryInterfaceFactory $categoryInterfaceFactory */
$categoryInterfaceFactory = $objectManager->get(CategoryInterfaceFactory::class);

$category = $categoryInterfaceFactory->create();
$category->isObjectNew(true);
$category->setId(333)
    ->setCreatedAt('2014-06-23 09:50:07')
    ->setName('Category 1')
    ->setParentId(2)
    ->setPath('1/2/333')
    ->setLevel(2)
    ->setAvailableSortBy(['position', 'name'])
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->setPostedProducts(
        [
            $simple1->getId() => 10,
            $simple2->getId() => 11,
            $simple3->getId() => 12
        ]
    );
$category->save();

/** @var Collection $indexerCollection */
$indexerCollection = $objectManager->get(Collection::class);
$indexerCollection->load();
/** @var Indexer $indexer */
foreach ($indexerCollection->getItems() as $indexer) {
    $indexer->reindexAll();
}
