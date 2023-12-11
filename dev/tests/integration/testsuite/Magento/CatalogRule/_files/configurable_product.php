<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_attribute.php');
Resolver::getInstance()->requireDataFixture('Magento/CatalogRule/_files/simple_products.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$storeManager = $objectManager->get(\Magento\Store\Model\StoreManager::class);
$store = $storeManager->getStore('default');
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$installer = $objectManager->get(\Magento\Catalog\Setup\CategorySetup::class);
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$attributeValues = [];
$associatedProductIds = [];

$attributeRepository = $objectManager->get(\Magento\Eav\Api\AttributeRepositoryInterface::class);
$attribute = $attributeRepository->get('catalog_product', 'test_configurable');
$options = $attribute->getOptions();
array_shift($options); //remove the first option which is empty
foreach (['simple1', 'simple2'] as $sku) {
    $option = array_shift($options);
    $product = $productRepository->get($sku);
    $product->setTestConfigurable($option->getValue());
    $productRepository->save($product);
    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $product->getId();
}

$product = $objectManager->create(\Magento\Catalog\Model\Product::class)
    ->setTypeId('configurable')
    ->setId(666)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ]);
$configurableAttributesData = [
    [
        'attribute_id' => $attribute->getId(),
        'code' => $attribute->getAttributeCode(),
        'label' => $attribute->getStoreLabel(),
        'position' => '0',
        'values' => $attributeValues,
    ],
];
$optionsFactory = $objectManager->get(\Magento\ConfigurableProduct\Helper\Product\Options\Factory::class);
$configurableOptions = $optionsFactory->create($configurableAttributesData);
$extensionConfigurableAttributes = $product->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
$product->setExtensionAttributes($extensionConfigurableAttributes);
$productRepository->save($product);
