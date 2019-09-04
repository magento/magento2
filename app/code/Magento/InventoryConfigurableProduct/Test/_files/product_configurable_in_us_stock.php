<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Inventory\Model\SourceItem\Command\SourceItemsSave;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
/** @var SourceItemsSave $sourceItemSave */
$sourceItemSave = $objectManager->get(SourceItemsSave::class);
/** @var CategorySetup $installer */
$installer = $objectManager->get(CategorySetup::class);
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
/** @var SourceItemInterfaceFactory $sourceItemFactory */
$sourceItemFactory = $objectManager->get(SourceItemInterfaceFactory::class);
/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->get(Factory::class);




/**
 * Simple products per each option value.
 */
$website = $websiteRepository->get('us_website');
$websiteIds = [$website->getId()];

$attribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable');
$options = $attribute->getOptions();
array_shift($options); //remove the first option which is empty

$attributeValues = [];
$attributeSetId = $installer->getAttributeSetId(Product::ENTITY, 'Default');
$associatedProductIds = [];
$productPrices = [10, 20];

foreach ($options as $option) {
    $product = $productFactory->create();
    $productPrice = array_shift($productPrices);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds($websiteIds)
        ->setName('Configurable Option' . $option->getLabel())
        ->setSku('simple_' . $productPrice)
        ->setPrice($productPrice)
        ->setCustomAttribute('test_configurable', $option->getValue())
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED);

    $product = $productRepository->save($product);

    /**
     * Stock status and qty for "simple_10" and "simple_20" products in "us-1" source.
     */
    $sourceItem = $sourceItemFactory->create();
    $sourceItem->setSku($product->getSku());
    $sourceItem->setSourceCode('us-1');
    $sourceItem->setQuantity(1000);
    $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);

    $sourceItemSave->execute([$sourceItem]);

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $product->getId();
}

/**
 * Configurable product.
 */
$product = $productFactory->create();

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
    ->setWebsiteIds($websiteIds)
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);

$productRepository->save($product);
