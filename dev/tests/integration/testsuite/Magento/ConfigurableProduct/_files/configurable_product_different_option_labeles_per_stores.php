<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/configurable_attribute_different_labels_per_stores.php';

$objectManager = Bootstrap::getObjectManager();
/** @var ProductAttributeRepositoryInterface $productAttributeRepository */
$productAttributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
$attribute = $productAttributeRepository->get('different_labels_attribute');
$options = $attribute->getOptions();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsite = $websiteRepository->get('base');
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
$attributeValues = [];
$associatedProductIds = [];
$rootCategoryId = $baseWebsite->getDefaultStore()->getRootCategoryId();
array_shift($options);

foreach ($options as $option) {
    $product = $productFactory->create();
    $product->setTypeId(ProductType::TYPE_SIMPLE)
        ->setAttributeSetId($product->getDefaultAttributeSetId())
        ->setWebsiteIds([$baseWebsite->getId()])
        ->setName('Configurable Option ' . $option->getLabel())
        ->setSku(strtolower(str_replace(' ', '_', 'simple ' . $option->getLabel())))
        ->setPrice(150)
        ->setDifferentLabelsAttribute($option->getValue())
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED)
        ->setCategoryIds([$rootCategoryId])
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $product = $productRepository->save($product);

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $product->getId();
}
/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->get(Factory::class);
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

$product = $productFactory->create();
/** @var ProductExtensionFactory $extensionAttributesFactory */
$extensionAttributesFactory = $objectManager->get(ProductExtensionFactory::class);
$extensionConfigurableAttributes = $product->getExtensionAttributes() ?: $extensionAttributesFactory->create();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
$product->setExtensionAttributes($extensionConfigurableAttributes);

$product->setTypeId(Configurable::TYPE_CODE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$baseWebsite->getId()])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setCategoryIds([$rootCategoryId])
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->save($product);
