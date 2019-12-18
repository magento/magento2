<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/product_text_swatch_attribute.php';
require __DIR__ . '/product_visual_swatch_attribute.php';

$objectManager = Bootstrap::getObjectManager();

$installer = $objectManager->create(CategorySetup::class);

$eavConfig = $objectManager->get(Config::class);
$attribute = $eavConfig->getAttribute(Product::ENTITY, 'text_swatch_attribute');
$secondAttribute = $eavConfig->getAttribute(Product::ENTITY, 'visual_swatch_attribute');
$options = $attribute->getOptions();
$secondAttributeOptions = $secondAttribute->getOptions();

$websiteRepository = $objectManager->create(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');

$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$attributeValues = [];
$secondAttributeValues = [];
$associatedProductIds = [];
$associatedProductIdsViaSecondAttribute = [];
$attributeSetId = $installer->getAttributeSetId(Product::ENTITY, 'Default');
$productFactory = $objectManager->get(ProductFactory::class);
array_shift($options);
array_shift($secondAttributeOptions);

foreach ($options as $option) {
    foreach ($secondAttributeOptions as $secondAttrOption) {
        $product = $productFactory->create();
        $product->setTypeId(ProductType::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([$baseWebsite->getId()])
            ->setName('Configurable Option ' . $option->getLabel())
            ->setSku(
                strtolower(
                    str_replace(' ', '_', 'simple ' . $option->getLabel() . '_' . $secondAttrOption->getLabel())
                )
            )
            ->setPrice(150)
            ->setTextSwatchAttribute($option->getValue())
            ->setVisualSwatchAttribute($secondAttrOption->getValue())
            ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
        $product = $productRepository->save($product, true);
        $associatedProductIds[] = $product->getId();
    }

    $attributeValues[] = [
        'label' => 'test1',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
}
foreach ($secondAttributeOptions as $secondAttrOption) {
    $secondAttributeValues[] = [
        'label' => 'test2',
        'attribute_id' => $secondAttribute->getId(),
        'value_index' => $secondAttrOption->getValue(),
    ];
}

$allAttributes = [$attribute, $secondAttribute];
$optionsFactory = $objectManager->create(Factory::class);

foreach ($allAttributes as $attribute) {
    $configurableAttributesData[] =
        [
            'attribute_id' => $attribute->getId(),
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel(),
            'position' => '0',
            'values' => $attribute->getAttributeCode() === 'text_swatch_attribute'
                ? $attributeValues
                : $secondAttributeValues,
        ];

}

$configurableOptions = $optionsFactory->create($configurableAttributesData);
$product = $objectManager->create(Product::class);
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
$productRepository->save($product);

$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [$baseWebsite->getDefaultStore()->getRootCategoryId()]
);
