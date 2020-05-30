<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as OptionCollection;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\Collection as IndexerCollection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/multiselect_attribute.php');
/** Create product with options and multiselect attribute */

$objectManager = Bootstrap::getObjectManager();
/** @var CategorySetup $installer */
$installer = $objectManager->create(CategorySetup::class);

/** @var OptionCollection $options */
$options = $objectManager->create(OptionCollection::class);
$eavConfig = $objectManager->get(EavConfig::class);

/** @var $attribute EavAttribute */
$attribute = $eavConfig->getAttribute('catalog_product', 'multiselect_attribute');

$eavConfig->clear();
$attribute->setIsSearchable(1)
    ->setIsVisibleInAdvancedSearch(1)
    ->setIsFilterable(false)
    ->setIsFilterableInSearch(false)
    ->setIsVisibleOnFront(0);
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(AttributeRepositoryInterface::class);
$attributeRepository->save($attribute);

$options->setAttributeFilter($attribute->getId());
$optionIds = $options->getAllIds();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var Product $product */
$product = $objectManager->create(Product::class);
$product->setTypeId(ProductType::TYPE_SIMPLE)
    ->setId($optionIds[0] * 10)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds([1])
    ->setName('With Multiselect 1 and 2')
    ->setSku('simple_ms_1')
    ->setPrice(10)
    ->setDescription('Hello " &amp;" Bring the water bottle when you can!')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setMultiselectAttribute([$optionIds[1],$optionIds[2]])
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
$productRepository->save($product);

$product = $objectManager->create(Product::class);
$product->setTypeId(ProductType::TYPE_SIMPLE)
    ->setId($optionIds[1] * 10)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds([1])
    ->setName('With Multiselect 2 and 3')
    ->setSku('simple_ms_2')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setMultiselectAttribute([$optionIds[2], $optionIds[3]])
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
$productRepository->save($product);

$product = $objectManager->create(Product::class);
$product->setTypeId(ProductType::TYPE_SIMPLE)
    ->setId($optionIds[2] * 10)
    ->setAttributeSetId($installer->getAttributeSetId('catalog_product', 'Default'))
    ->setWebsiteIds([1])
    ->setName('With Multiselect 1 and 3')
    ->setSku('simple_ms_2')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setMultiselectAttribute([$optionIds[2], $optionIds[3]])
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

$productRepository->save($product);

/** @var IndexerCollection $indexerCollection */
$indexerCollection = $objectManager->get(IndexerCollection::class);
$indexerCollection->load();
/** @var Indexer $indexer */
foreach ($indexerCollection->getItems() as $indexer) {
    $indexer->reindexAll();
}
