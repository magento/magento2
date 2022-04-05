<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Indexer\Model\Indexer\Collection as IndexerCollection;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/layered_navigation_attribute.php');

$objectManager = Bootstrap::getObjectManager();

$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');

$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
$attribute = $attributeRepository->get('test_configurable');

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $objectManager->create(Product::class);
$product->setTypeId(ProductType::TYPE_SIMPLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setName('Not visible simple')
    ->setSku('not_visible_simple')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setPrice(10)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(ProductVisibility::VISIBILITY_NOT_VISIBLE)
    ->setStatus(ProductStatus::STATUS_ENABLED)
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setData($attribute->getAttributeCode(), $attribute->getSource()->getOptionId('Option 1'));
$productRepository->save($product);

$indexerCollection = $objectManager->get(IndexerCollection::class);
foreach ($indexerCollection->getItems() as $indexer) {
    $indexer->reindexAll();
}
