<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/configurable_attribute.php';

/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Setup\CategorySetup');

/* Create simple products per each option value*/
/** @var \Magento\Eav\Api\Data\AttributeOptionInterface[] $options */
$options = $attribute->getOptions();

$attributeValues = [];
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$associatedProductIds = [];
$productIds = [100, 200];
array_shift($options); //remove the first option which is empty
foreach ($options as $option) {
    /** @var $product \Magento\Catalog\Model\Product */
    $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
    $productId = array_shift($productIds);
    $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([1])
        ->setName('Configurable Option' . $option->getLabel())
        ->setSku('simple_' . $productId)
        ->setPrice(10)
        ->setTestConfigurable($option->getValue())
        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
        ->save();

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $product->getId();
}

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setPrice(100)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1])
    ->setAssociatedProductIds($associatedProductIds)
    ->setConfigurableAttributesData(
        [
            [
                'attribute_id' => $attribute->getId(),
                'attribute_code' => $attribute->getAttributeCode(),
                'frontend_label' => 'test',
                'values' => $attributeValues,
            ],
        ]
    )
    ->setCanSaveCustomOptions(true)
    ->setProductOptions(
        [
            [
                'id'        => 1,
                'option_id' => 0,
                'previous_group' => 'text',
                'title'     => 'Test Field',
                'type'      => 'field',
                'is_require' => 1,
                'sort_order' => 0,
                'price'     => 1,
                'price_type' => 'fixed',
                'sku'       => '1-text',
                'max_characters' => 100,
            ],
            [
                'id'        => 2,
                'option_id' => 0,
                'previous_group' => 'date',
                'title'     => 'Test Date and Time',
                'type'      => 'date_time',
                'is_require' => 1,
                'sort_order' => 0,
                'price'     => 2,
                'price_type' => 'fixed',
                'sku'       => '2-date',
            ],
            [
                'id'        => 3,
                'option_id' => 0,
                'previous_group' => 'select',
                'title'     => 'Test Select',
                'type'      => 'drop_down',
                'is_require' => 1,
                'sort_order' => 0,
                'values'    => [
                    [
                        'option_type_id' => -1,
                        'title'         => 'Option 1',
                        'price'         => 3,
                        'price_type'    => 'fixed',
                        'sku'           => '3-1-select',
                    ],
                    [
                        'option_type_id' => -1,
                        'title'         => 'Option 2',
                        'price'         => 3,
                        'price_type'    => 'fixed',
                        'sku'           => '3-2-select',
                    ],
                ]
            ],
            [
                'id'        => 4,
                'option_id' => 0,
                'previous_group' => 'select',
                'title'     => 'Test Radio',
                'type'      => 'radio',
                'is_require' => 1,
                'sort_order' => 0,
                'values'    => [
                    [
                        'option_type_id' => -1,
                        'title'         => 'Option 1',
                        'price'         => 3,
                        'price_type'    => 'fixed',
                        'sku'           => '4-1-radio',
                    ],
                    [
                        'option_type_id' => -1,
                        'title'         => 'Option 2',
                        'price'         => 3,
                        'price_type'    => 'fixed',
                        'sku'           => '4-2-radio',
                    ],
                ]
            ],
        ]
    )
    ->setHasOptions(true)
    ->save();
