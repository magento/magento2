<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(
        \Magento\Catalog\Setup\CategorySetup::class
    );

/** @var $attributes array */
$attributes = [
    [
        'code' => 'test_attribute_1',
        'label' => 'Test attribute 1'
    ],
    [
        'code' => 'test_attribute_2',
        'label' => 'Test attribute 2'
    ]
];

foreach ($attributes as $item) {
    $code = $item['code'];
    $label = $item['label'];
    $attribute = $eavConfig->getAttribute('catalog_product', $code);
    if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
        && $attribute->getId()
    ) {
        $attribute->delete();
    }

    $eavConfig->clear();

    /* Create attribute */
    /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
    $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
    );

    $attribute->setData(
        [
            'attribute_code' => $code,
            'entity_type_id' => 4,
            'is_global' => 1,
            'is_user_defined' => 1,
            'frontend_input' => 'select',
            'is_unique' => 0,
            'is_required' => 0,
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
            'frontend_label' => [$label],
            'backend_type' => 'int',
            'option' => [
                'value' => ['option_0' => ['Option 1'], 'option_1' => ['Option 2']],
                'order' => ['option_0' => 1, 'option_1' => 2],
            ],
        ]
    );

    $attribute->save();

    /* Assign attribute to attribute set */
    $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());
}

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$eavConfig->clear();
