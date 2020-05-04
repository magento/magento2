<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Eav\Model\Config;

Resolver::getInstance()->requireDataFixture('Magento/Elasticsearch/_files/select_attribute.php');
Resolver::getInstance()->requireDataFixture('Magento/Elasticsearch/_files/multiselect_attribute.php');
Resolver::getInstance()->requireDataFixture('Magento/Elasticsearch/_files/configurable_attribute.php');

$objectManager = Bootstrap::getObjectManager();

$eavConfig = Bootstrap::getObjectManager()->get(Config::class);
$attribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable');
$selectAttribute = $eavConfig->getAttribute(Product::ENTITY, 'select_attribute');
$multiselectAttribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable');
/** @var CategorySetup $installer */
$installer = $objectManager->create(CategorySetup::class);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');

/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();
$selectOptionsIds = $objectManager->create(Collection::class)
    ->setAttributeFilter($selectAttribute->getId())
    ->getAllIds();
$multiselectOptionsIds = $objectManager->create(Collection::class)
    ->setAttributeFilter($multiselectAttribute->getId())
    ->getAllIds();
$attributeValues = [];
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$associatedProductIds = [];
$productIds = [10, 20];
array_shift($options);
foreach ($options as $option) {
    /** @var Product $product */
    $product = $objectManager->create(Product::class);
    $productId = array_shift($productIds);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setId($productId)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([1])
        ->setName('Configurable Option Product' . str_replace(' ', '', $option->getLabel()))
        ->setSku('simple_' . $productId)
        ->setPrice($productId)
        ->setTestConfigurable($option->getValue())
        ->setShortDescription('simpledescription')
        ->setMultiselectAttribute([$multiselectOptionsIds[0]])
        ->setSelectAttribute([$selectOptionsIds[0]])
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(
            [
                'use_config_manage_stock' => 1,
                'qty' => 100,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1
            ]
        );
    $product = $productRepository->save($product);

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $product->getId();
}

/** @var Product $product */
$product = $objectManager->create(Product::class);
/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->create(Factory::class);
$configurableAttributesData = [
    [
        'attribute_id' => $attribute->getId(),
        'code' => $attribute->getAttributeCode(),
        'label' => $attribute->getStoreLabel(),
        'position' => '0',
        'values' => $attributeValues,
    ],
];
$configurableOptions = $optionsFactory->create($configurableAttributesData);
$extensionConfigurableAttributes = $product->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
$product->setExtensionAttributes($extensionConfigurableAttributes);

$product->setTypeId(Configurable::TYPE_CODE)
    ->setId(1)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->cleanCache();
$productRepository->save($product);
