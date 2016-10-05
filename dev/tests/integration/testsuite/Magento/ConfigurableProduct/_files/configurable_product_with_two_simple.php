<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/configurable_attribute.php';

$objectManager = Bootstrap::getObjectManager();
/** @var DataObjectHelper $dataObjectHelper */
$dataObjectHelper = $objectManager->get(DataObjectHelper::class);
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
/** @var CategorySetup $installer */
$installer = Bootstrap::getObjectManager()->create(CategorySetup::class);
/** @var Factory $optionsFactory */
$optionsFactory = Bootstrap::getObjectManager()->create(Factory::class);

/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$configurableProductLinks = [];
$attributeValues = [];
$i = 1;
array_shift($options); // remove the first option which is empty
foreach ($options as $option) {
    /** @var ProductInterface $simpleProduct */
    $simpleProduct = $productFactory->create();
    $dataObjectHelper->populateWithArray(
        $simpleProduct,
        [
            ProductInterface::TYPE_ID => Type::TYPE_VIRTUAL,
            ProductInterface::ATTRIBUTE_SET_ID => $attributeSetId,
            'website_ids' => [1],
            ProductInterface::NAME => 'Configurable Option' . $option->getLabel(),
            ProductInterface::SKU => 'sku_' . ($i * 10),
            ProductInterface::PRICE => ($i * 10),
            ProductInterface::VISIBILITY => Visibility::VISIBILITY_NOT_VISIBLE,
            ProductInterface::STATUS => Status::STATUS_ENABLED,
            ProductInterface::CUSTOM_ATTRIBUTES => [
                [
                    'attribute_code' => $attribute->getAttributeCode(),
                    'value' => $option->getValue(),
                ],
            ],
            ProductInterface::EXTENSION_ATTRIBUTES_KEY => [
                'stock_item' => [
                    StockItemInterface::QTY => 1000,
                    StockItemInterface::IS_IN_STOCK => true,
                ],
            ],
        ],
        ProductInterface::class
    );
    $simpleProduct = $productRepository->save($simpleProduct);

    $configurableProductLinks[] = $simpleProduct->getId();
    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $i++;
}

/** @var ProductInterface $configurableProduct */
$configurableProduct = $productFactory->create();
$dataObjectHelper->populateWithArray(
    $configurableProduct,
    [
        ProductInterface::TYPE_ID => 'configurable',
        ProductInterface::ATTRIBUTE_SET_ID => $attributeSetId,
        'website_ids' => [1],
        ProductInterface::NAME => 'Configurable Product',
        ProductInterface::SKU => 'configurable_product_with_two_simple',
        ProductInterface::VISIBILITY => Visibility::VISIBILITY_BOTH,
        ProductInterface::STATUS => Status::STATUS_ENABLED,
    ],
    ProductInterface::class
);
$extensionAttributes = $configurableProduct->getExtensionAttributes();
$configurableProductOptions = $optionsFactory->create([
    [
        'attribute_id' => $attribute->getId(),
        'code' => $attribute->getAttributeCode(),
        'label' => $attribute->getStoreLabel(),
        'position' => '0',
        'values' => $attributeValues,
    ],
]);
$extensionAttributes->setConfigurableProductOptions($configurableProductOptions);
$extensionAttributes->setConfigurableProductLinks($configurableProductLinks);
$configurableProduct->setExtensionAttributes($extensionAttributes);

$configurableProduct = $productRepository->save($configurableProduct);
