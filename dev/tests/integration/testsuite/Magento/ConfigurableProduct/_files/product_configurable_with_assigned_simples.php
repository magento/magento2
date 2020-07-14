<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceIndexerProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Bootstrap::getInstance()->reinitialize();

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_attribute.php');

$installer = Bootstrap::getObjectManager()->create(CategorySetup::class);
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');

$eavConfig = Bootstrap::getObjectManager()->get(Config::class);
$attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');

$product = Bootstrap::getObjectManager()->create(Product::class);
$product->setTypeId(Configurable::TYPE_CODE)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
$product = $productRepository->save($product);

$attributeValues = [];
$associatedProductIds = [];
/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();
array_shift($options); //remove the first option which is empty
$productNumber = 0;
foreach ($options as $option) {
    $productNumber++;

    $childProduct = Bootstrap::getObjectManager()->create(Product::class);
    $childProduct->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([1])
        ->setName('Configurable Option' . $option->getLabel())
        ->setSku('simple_' . $productNumber)
        ->setPrice($productNumber * 10)
        ->setTestConfigurable($option->getValue())
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(
            ['use_config_manage_stock' => 1,'qty' => $productNumber * 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]
        );
    $childProduct = $productRepository->save($childProduct);

    $stockItem = Bootstrap::getObjectManager()->create(Item::class);
    $stockItem->load($childProduct->getId(), 'product_id');
    if (!$stockItem->getProductId()) {
        $stockItem->setProductId($childProduct->getId());
    }
    $stockItem->setUseConfigManageStock(1);
    $stockItem->setQty($productNumber * 100);
    $stockItem->setIsQtyDecimal(0);
    $stockItem->setIsInStock(1);
    $stockItem->save();

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $childProduct->getId();
}

$indexerProcessor = Bootstrap::getObjectManager()->get(PriceIndexerProcessor::class);
$indexerProcessor->reindexList($associatedProductIds);

$optionsFactory = Bootstrap::getObjectManager()->create(Factory::class);
$configurableOptions = $optionsFactory->create(
    [
        [
            'attribute_id' => $attribute->getId(),
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel(),
            'position' => '0',
            'values' => $attributeValues,
        ],
    ]
);
$extensionConfigurableAttributes = $product->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
$product->setExtensionAttributes($extensionConfigurableAttributes);
$product = $productRepository->save($product);

$indexerProcessor = Bootstrap::getObjectManager()->get(PriceIndexerProcessor::class);
$indexerProcessor->reindexRow($product->getId());
