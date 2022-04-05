<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Indexer\Model\Indexer\Collection as IndexerCollection;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/CatalogSearch/_files/searchable_attribute.php');

$objectManager = Bootstrap::getObjectManager();

$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');

$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
$attribute = $attributeRepository->get('test_searchable_attribute');

$product = $objectManager->create(Product::class);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Simple product name')
    ->setSku('simple_for_search')
    ->setPrice(100)
    ->setWeight(1)
    ->setShortDescription('Product short description')
    ->setTaxClassId(0)
    ->setDescription('Product description')
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setTestSearchableAttribute($attribute->getSource()->getOptionId('Option 1'))
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]
    );
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->save($product);

//$indexerCollection = $objectManager->get(IndexerCollection::class);
//foreach ($indexerCollection->getItems() as $indexer) {
//    $indexer->reindexAll();
//}
