<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;

$attributes = [
    'country_of_manufacture',
    'group_price',
    'minimal_price',
    'msrp',
    'msrp_display_actual_price_type',
    'price',
    'special_price',
    'special_from_date',
    'special_to_date',
    'tier_price',
    'weight',
];
foreach ($attributes as $attributeCode) {
    $relatedProductTypes = explode(
        ',',
        $installer->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, 'apply_to')
    );
    if (!in_array(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE, $relatedProductTypes)) {
        $relatedProductTypes[] = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
        $installer->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeCode,
            'apply_to',
            implode(',', $relatedProductTypes)
        );
    }
}
