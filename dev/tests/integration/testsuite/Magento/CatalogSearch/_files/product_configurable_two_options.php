<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/ConfigurableProduct/_files/configurable_attribute.php';
require __DIR__ . '/../../../Magento/ConfigurableProduct/_files/configurable_attribute_2.php';

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\Search\Request\Config\Converter;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(AttributeRepositoryInterface::class);
/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);
$attributes = ['test_configurable', 'test_configurable_2'];
foreach ($attributes as $attributeName) {
    $attributeModel = $eavConfig->getAttribute(Product::ENTITY, $attributeName);
    $attributeModel->addData([
        'is_searchable' => 1,
        'is_filterable' => 1,
        'is_filterable_in_search' => 1,
        'is_visible_in_advanced_search' => 1,
    ]);
    $attributeRepository->save($attributeModel);
}

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

/** @var $installer CategorySetup */
$installer = $objectManager->create(CategorySetup::class);

/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();

$attribute1Values = [];
$attribute2Values = [];
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$associatedProductIds = [];
array_shift($options);
$index1 = 1;
foreach ($options as $option1) {
    /** @var AttributeOptionInterface[] $options */
    $options2 = $attribute2->getOptions();
    array_shift($options2);
    $index2 = 1;
    foreach ($options2 as $option2) {
        /** @var $product Product */
        $product = $objectManager->create(Product::class);
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([1])
            ->setName('Configurable2 Option' . $index1 . $index2)
            ->setSku('configurable2_option_' . $index1 . $index2)
            ->setPrice(random_int(10, 100))
            ->setTestConfigurable($option1->getValue())
            ->setTestConfigurable2($option2->getValue())
            ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

        $product = $productRepository->save($product);

        /** @var Item $stockItem */
        $stockItem = $objectManager->create(Item::class);
        $stockItem->load($product->getId(), 'product_id');

        if (!$stockItem->getProductId()) {
            $stockItem->setProductId($product->getId());
        }
        $stockItem->setUseConfigManageStock(1);
        $stockItem->setQty(1000);
        $stockItem->setIsQtyDecimal(0);
        $stockItem->setIsInStock(1);
        $stockItem->save();

        $attribute1Values[] = [
            'label' => 'test1',
            'attribute_id' => $attribute->getId(),
            'value_index' => $option1->getValue(),
        ];
        $attribute2Values[] = [
            'label' => 'test2',
            'attribute_id' => $attribute2->getId(),
            'value_index' => $option2->getValue(),
        ];
        $associatedProductIds[] = $product->getId();
        $index2++;
    }
    $index1++;
}

/** @var $product Product */
$product = $objectManager->create(Product::class);

/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->create(Factory::class);

$configurableAttributesData = [
    [
        'attribute_id' => $attribute->getId(),
        'code' => $attribute->getAttributeCode(),
        'label' => $attribute->getStoreLabel(),
        'position' => '0',
        'values' => $attribute1Values,
    ],
    [
        'attribute_id' => $attribute2->getId(),
        'code' => $attribute2->getAttributeCode(),
        'label' => $attribute2->getStoreLabel(),
        'position' => '1',
        'values' => $attribute2Values,
    ],
];

$configurableOptions = $optionsFactory->create($configurableAttributesData);

$extensionConfigurableAttributes = $product->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);

$product->setExtensionAttributes($extensionConfigurableAttributes);

$product->setTypeId(Configurable::TYPE_CODE)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('configurable with 2 opts')
    ->setSku('configurable_with_2_opts')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);

$productRepository->save($product);

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2]
);

/** @var Converter $converter */
$converter = $objectManager->create(Converter::class);
$document = new DOMDocument();
$document->load(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'requests.xml');
$requestConfig = $converter->convert($document);
/** @var Config $config */
$config = $objectManager->get(Config::class);
$config->merge($requestConfig);
