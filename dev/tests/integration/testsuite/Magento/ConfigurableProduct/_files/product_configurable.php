<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/configurable_attribute.php';

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Setup',
    ['resourceName' => 'catalog_setup']
);

/* Create simple products per each option */
/** @var $options \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection */
$options = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection'
);
$options->setAttributeFilter($attribute->getId());

$attributeValues = [];
$productIds = [];
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$productIds = [10, 20];
foreach ($options as $option) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    $productId = array_shift($productIds);
    $product->setTypeId(
        \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
    )->setId(
        $productId
    )->setAttributeSetId(
        $attributeSetId
    )->setWebsiteIds(
        [1]
    )->setName(
        'Configurable Option' . $option->getId()
    )->setSku(
        'simple_' . $productId
    )->setPrice(
        10
    )->setTestConfigurable(
        $option->getId()
    )->setVisibility(
        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
    )->setStatus(
        \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
    )->setStockData(
        ['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]
    )->save();

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getId(),
        'is_percent' => false,
        'pricing_value' => 5,
    ];
    $productIds[] = $product->getId();
}

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
)->setId(
    1
)->setAttributeSetId(
    $attributeSetId
)->setWebsiteIds(
    [1]
)->setName(
    'Configurable Product'
)->setSku(
    'configurable'
)->setPrice(
    100
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setStockData(
    ['use_config_manage_stock' => 1, 'is_in_stock' => 1]
)->setAssociatedProductIds(
    $productIds
)->setConfigurableAttributesData(
    [
        [
            'attribute_id' => $attribute->getId(),
            'attribute_code' => $attribute->getAttributeCode(),
            'frontend_label' => 'test',
            'values' => $attributeValues,
        ],
    ]
)->save();
