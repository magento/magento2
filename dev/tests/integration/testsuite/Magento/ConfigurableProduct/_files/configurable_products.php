<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_attribute.php');

$objectManager = Bootstrap::getObjectManager();

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductInterfaceFactory $productInterfaceFactory */
$productInterfaceFactory = $objectManager->get(ProductInterfaceFactory::class);

/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
/** @var $attribute Attribute */
$attribute = $attributeRepository->get('test_configurable');
/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();

/** @var $installer EavSetup */
$installer = $objectManager->get(EavSetup::class);
$attributeSetId = $installer->getAttributeSetId(Product::ENTITY, 'Default');

/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->get(Factory::class);
/* Create simple products per each option value*/

$attributeValues = [];
$associatedProductIds = [];
$productIds = [10, 20];
array_shift($options); //remove the first option which is empty

foreach ($options as $option) {
    /** @var $product Product */
    $product = $productInterfaceFactory->create();
    $productId = array_shift($productIds);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([$baseWebsite->getId()])
        ->setName('Configurable Option' . $option->getLabel())
        ->setSku('simple_' . $productId)
        ->setPrice($productId)
        ->setTestConfigurable($option->getValue())
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $simple1 = $productRepository->save($product);

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $simple1->getId();
}

/** @var $product Product */
$product = $productInterfaceFactory->create();
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
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->cleanCache();
$productRepository->save($product);

/* Create simple products per each option value*/
/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();

$attributeValues = [];
$associatedProductIds = [];
$productIds = [30, 40];
array_shift($options); //remove the first option which is empty

foreach ($options as $option) {
    /** @var $product Product */
    $product = $productInterfaceFactory->create();
    $productId = array_shift($productIds);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([$baseWebsite->getId()])
        ->setName('Configurable Option' . $option->getLabel())
        ->setSku('simple_' . $productId)
        ->setPrice($productId)
        ->setTestConfigurable($option->getValue())
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $simple2 = $productRepository->save($product);

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $simple2->getId();
}

/** @var $product Product */
$product = $productInterfaceFactory->create();

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
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Configurable Product 12345')
    ->setSku('configurable_12345')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->cleanCache();
$productRepository->save($product);
