<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../ConfigurableProduct/_files/configurable_attribute.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$storeManager = $objectManager->get(\Magento\Store\Model\StoreManager::class);
$store = $storeManager->getStore('default');

$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$installer = $objectManager->get(\Magento\Catalog\Setup\CategorySetup::class);
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$attributeValues = [];
$associatedProductIds = [];

/** @var Magento\Eav\Model\Entity\Attribute\Option[] $options */
$options = $attribute->getOptions();
array_shift($options); //remove the first option which is empty

$product = $objectManager->create(\Magento\Catalog\Model\Product::class)
    ->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Simple Product 1')
    ->setSku('simple1')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ]);
$option = array_shift($options);
$product->setTestConfigurable($option->getValue());
$productRepository->save($product);
$attributeValues[] = [
    'label' => 'test',
    'attribute_id' => $attribute->getId(),
    'value_index' => $option->getValue(),
];
$associatedProductIds[] = $product->getId();
$productAction = $objectManager->get(\Magento\Catalog\Model\Product\Action::class);
$productAction->updateAttributes([$product->getId()], ['test_attribute' => 'test_attribute_value'], $store->getId());

$product = $objectManager->create(\Magento\Catalog\Model\Product::class)
    ->setTypeId('simple')
    ->setId(2)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Simple Product 2')
    ->setSku('simple2')
    ->setPrice(9.9)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ]);
$option = array_shift($options);
$product->setTestConfigurable($option->getValue());
$productRepository->save($product);
$attributeValues[] = [
    'label' => 'test',
    'attribute_id' => $attribute->getId(),
    'value_index' => $option->getValue(),
];
$associatedProductIds[] = $product->getId();

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
        'is_in_stock' => 0,
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
