<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;
/**
 * Add attributes to the eav/attribute table
 */
$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'links_purchased_separately',
    [
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Links can be purchased separately',
        'input' => '',
        'class' => '',
        'source' => '',
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL,
        'visible' => false,
        'required' => true,
        'user_defined' => false,
        'default' => '',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'apply_to' => 'downloadable',
        'used_in_product_listing' => true
    ]
);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'samples_title',
    [
        'type' => 'varchar',
        'backend' => '',
        'frontend' => '',
        'label' => 'Samples title',
        'input' => '',
        'class' => '',
        'source' => '',
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
        'visible' => false,
        'required' => true,
        'user_defined' => false,
        'default' => '',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'apply_to' => 'downloadable'
    ]
);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'links_title',
    [
        'type' => 'varchar',
        'backend' => '',
        'frontend' => '',
        'label' => 'Links title',
        'input' => '',
        'class' => '',
        'source' => '',
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
        'visible' => false,
        'required' => true,
        'user_defined' => false,
        'default' => '',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'apply_to' => 'downloadable'
    ]
);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'links_exist',
    [
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => '',
        'input' => '',
        'class' => '',
        'source' => '',
        'global' => true,
        'visible' => false,
        'required' => false,
        'user_defined' => false,
        'default' => '0',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'unique' => false,
        'apply_to' => 'downloadable',
        'used_in_product_listing' => 1
    ]
);

$fieldList = [
    'price',
    'special_price',
    'special_from_date',
    'special_to_date',
    'minimal_price',
    'cost',
    'tier_price',
    'group_price',
    'weight',
];

// make these attributes applicable to downloadable products
foreach ($fieldList as $field) {
    $applyTo = explode(',', $installer->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to'));
    if (!in_array('downloadable', $applyTo)) {
        $applyTo[] = 'downloadable';
        $installer->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $field,
            'apply_to',
            implode(',', $applyTo)
        );
    }
}
