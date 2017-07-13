<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* Create attribute */
/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Setup\CategorySetup::class
);

$attributesData = [
    [
        'attribute_code' => 'test_quick_search',
        'is_filterable_in_search' => 1,
    ],
    [
        'attribute_code' => 'test_catalog_view',
        'is_filterable' => 1,
    ]
];

foreach ($attributesData as $data) {

    /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
    $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
    );
    $attributeData = array_merge(
        [
            'attribute_code' => '',
            'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
            'is_global' => 1,
            'is_user_defined' => 1,
            'frontend_input' => 'select',
            'is_unique' => 0,
            'is_required' => 1,
            'is_searchable' => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'is_used_for_promo_rules' => 0,
            'is_html_allowed_on_front' => 1,
            'is_visible_on_front' => 0,
            'used_in_product_listing' => 0,
            'used_for_sort_by' => 0,
            'frontend_label' => ['Test Configurable'],
            'backend_type' => 'int',
            'option' => [
                'value' => ['option_0' => ['Option 1'], 'option_1' => ['Option 2']],
                'order' => ['option_0' => 1, 'option_1' => 2],
            ],
        ],
        $data
    );
    $attribute->setData($attributeData);
    $attribute->save();
    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());
}

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$eavConfig->clear();
