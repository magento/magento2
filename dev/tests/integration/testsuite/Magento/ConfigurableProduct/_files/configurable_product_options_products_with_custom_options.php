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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/configurable_attribute_first.php'
);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->get(ProductRepositoryInterface::class);

/** @var $installer CategorySetup */
$installer = Bootstrap::getObjectManager()->create(CategorySetup::class);

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$attribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable_first');

$simpleProductOptions = [
    [
        'title' => 'field option 10',
        'type' => 'field',
        'is_require' => true,
        'sort_order' => 1,
        'price' => -10.0,
        'price_type' => 'fixed',
        'sku' => 'field_option1',
        'product_sku' => 'simple_10',
        'max_characters' => 10,
    ],
    [
        'title' => 'field option 20',
        'type' => 'field',
        'is_require' => false,
        'sort_order' => 1,
        'price' => -10.0,
        'price_type' => 'fixed',
        'sku' => 'field_option2',
        'product_sku' => 'simple_20',
        'max_characters' => 10,
    ],
    [
        'title' => 'area option 30',
        'type' => 'area',
        'is_require' => false,
        'sort_order' => 2,
        'price' => 20.0,
        'price_type' => 'percent',
        'sku' => 'area_option',
        'product_sku' => 'simple_30',
        'max_characters' => 20
    ],
    [
        'title' => 'drop_down option 40',
        'type' => 'drop_down',
        'is_require' => false,
        'sort_order' => 4,
        'product_sku' => 'simple_40',
        'values' => [
            [
                'title' => 'drop_down option 1',
                'price' => 10,
                'price_type' => 'fixed',
                'sku' => 'drop_down_option1',
                'sort_order' => 1,
            ],
            [
                'title' => 'drop_down option 2',
                'price' => 20,
                'price_type' => 'fixed',
                'sku' => 'drop_down_option2',
                'sort_order' => 2,
            ],
        ],
    ],
];

/** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory::class);
$customOptions = [];

foreach ($simpleProductOptions as $option) {
    $productSku = $option['product_sku'];

    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
    $customOption = $customOptionFactory->create(['data' => $option]);
    $customOptions[$productSku][] = $customOption;
}

/* Create simple products per each option value*/
/** @var AttributeOptionInterface[] $attributeOptions */
$attributeOptions = $attribute->getOptions();

$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$associatedProductIds = [];
$productIds = range(10, 40, 10);
$attributeValues = [];
$i = 1;

foreach ($productIds as $productId) {
    $option = $attributeOptions[$i];
    /** @var $product Product */
    $product = Bootstrap::getObjectManager()->create(Product::class);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setId($productId)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([1])
        ->setName('Configurable Option ' . $option->getLabel())
        ->setSku('simple_' . $productId)
        ->setPrice($productId)
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED)
        ->setOptions($customOptions['simple_' . $productId])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $customAttributes = [
        $attribute->getAttributeCode() => $option->getValue(),
    ];

    foreach ($customAttributes as $attributeCode => $attributeValue) {
        $product->setCustomAttributes($customAttributes);
    }

    $product = $productRepository->save($product);

    $attributeValues[] = [
        'label' => 'test attribute ' . $i,
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];

    $associatedProductIds[] = $product->getId();
    $i++;
}

/** @var $product Product */
$product = Bootstrap::getObjectManager()->create(Product::class);
/** @var Factory $optionsFactory */
$optionsFactory = Bootstrap::getObjectManager()->create(Factory::class);
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
    ->setSku('configurable-product')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->cleanCache();
$productRepository->save($product);
