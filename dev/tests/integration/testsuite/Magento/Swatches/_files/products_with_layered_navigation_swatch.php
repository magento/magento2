<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Api\Data\AttributeOptionInterface;

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = Bootstrap::getObjectManager()->create(\Magento\Catalog\Setup\CategorySetup::class);

$data = [
    'attribute_code' => 'color_swatch',
    'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
    'is_global' => 1,
    'is_user_defined' => 1,
    'frontend_input' => 'select',
    'is_unique' => 0,
    'is_required' => 0,
    'is_searchable' => 1,
    'is_visible_in_advanced_search' => 1,
    'is_comparable' => 1,
    'is_filterable' => 1,
    'is_filterable_in_search' => 1,
    'is_used_for_promo_rules' => 0,
    'is_html_allowed_on_front' => 1,
    'is_visible_on_front' => 1,
    'used_in_product_listing' => 1,
    'used_for_sort_by' => 1,
    'frontend_label' => ['Test Swatch'],
    'backend_type' => 'int',
    'use_product_image_for_swatch' => 0,
    'update_product_preview_image' => 0,
];
$optionsPerAttribute = 3;

$data['swatch_input_type'] = 'visual';
$data['swatchvisual']['value'] = array_reduce(
    range(1, $optionsPerAttribute),
    function ($values, $index) use ($optionsPerAttribute) {
        $values['option_' . $index] = '#'
            . str_repeat(
                dechex(255 * $index / $optionsPerAttribute),
                3
            );
        return $values;
    },
    []
);
$data['optionvisual']['value'] = array_reduce(
    range(1, $optionsPerAttribute),
    function ($values, $index) use ($optionsPerAttribute) {
        $values['option_' . $index] = ['option ' . $index];
        return $values;
    },
    []
);

$data['options']['option'] = array_reduce(
    range(1, $optionsPerAttribute),
    function ($values, $index) use ($optionsPerAttribute) {
        $values[] = [
            'label' => 'option ' . $index,
            'value' => 'option_' . $index,
        ];
        return $values;
    },
    []
);

$options = [];
foreach ($data['options']['option'] as $optionData) {
    $options[] = $objectManager->get(AttributeOptionInterface::class)
        ->setLabel($optionData['label'])
        ->setValue($optionData['value']);
}

$attribute = $objectManager->create(
    \Magento\Catalog\Api\Data\ProductAttributeInterface::class,
    ['data' => $data]
);
$attribute->setOptions($options);
$attribute->save();

/* Assign attribute to attribute set */
$installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$eavConfig->clear();

$attribute = $eavConfig->getAttribute('catalog_product', 'color_swatch');
$options = $attribute->getOptions();

// workaround for saved attribute
$attribute->setDefaultValue($options[1]->getValue());

$attribute->save();
$eavConfig->clear();

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(10)
    ->setAttributeSetId(4)
    ->setName('Simple Product1')
    ->setSku('simple1')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(\Magento\Msrp\Model\Product\Attribute\Source\Type::TYPE_IN_CART)
    ->setPrice(10)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setSpecialPrice('5.99')
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(11)
    ->setAttributeSetId(4)
    ->setName('Simple Product2')
    ->setSku('simple2')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(\Magento\Msrp\Model\Product\Attribute\Source\Type::TYPE_ON_GESTURE)
    ->setPrice(20)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 50, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setSpecialPrice('15.99')
    ->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(12)
    ->setAttributeSetId(4)
    ->setName('Simple Product3')
    ->setSku('simple3')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setPrice(30)
    ->setWeight(1)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setCategoryIds([])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 140, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setSpecialPrice('25.99')
    ->save();

$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setId(
    333
)->setCreatedAt(
    '2014-06-23 09:50:07'
)->setName(
    'Category 1'
)->setParentId(
    2
)->setPath(
    '1/2/333'
)->setLevel(
    2
)->setAvailableSortBy(
    ['position', 'name']
)->setDefaultSortBy(
    'name'
)->setIsActive(
    true
)->setPosition(
    1
)->setPostedProducts(
    [10 => 10, 11 => 11, 12 => 12]
)->save();

/** @var \Magento\Indexer\Model\Indexer\Collection $indexerCollection */
$indexerCollection = Bootstrap::getObjectManager()->get(\Magento\Indexer\Model\Indexer\Collection::class);
$indexerCollection->load();
/** @var \Magento\Indexer\Model\Indexer $indexer */
foreach ($indexerCollection->getItems() as $indexer) {
    $indexer->reindexAll();
}
