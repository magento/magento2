<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Tax\Model\Resource\Setup */

/**
 * Add tax_class_id attribute to the 'eav_attribute' table
 */
$catalogInstaller = $this->getCatalogResourceSetup(['resourceName' => 'catalog_setup']);
$catalogInstaller->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'tax_class_id',
    [
        'group' => 'Product Details',
        'sort_order' => 40,
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Tax Class',
        'input' => 'select',
        'class' => '',
        'source' => 'Magento\Tax\Model\TaxClass\Source\Product',
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'default' => '2',
        'searchable' => true,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'visible_in_advanced_search' => true,
        'used_in_product_listing' => true,
        'unique' => false,
        'apply_to' => implode($this->getTaxableItems(), ',')
    ]
);

/**
 * install tax classes
 */
$data = [
    [
        'class_id' => 2,
        'class_name' => 'Taxable Goods',
        'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT,
    ],
    [
        'class_id' => 3,
        'class_name' => 'Retail Customer',
        'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER
    ],
];
foreach ($data as $row) {
    $this->getConnection()->insertForce($this->getTable('tax_class'), $row);
}

/**
 * install tax calculation rates
 */
$data = [
    [
        'tax_calculation_rate_id' => 1,
        'tax_country_id' => 'US',
        'tax_region_id' => 12,
        'tax_postcode' => '*',
        'code' => 'US-CA-*-Rate 1',
        'rate' => '8.2500',
    ],
    [
        'tax_calculation_rate_id' => 2,
        'tax_country_id' => 'US',
        'tax_region_id' => 43,
        'tax_postcode' => '*',
        'code' => 'US-NY-*-Rate 1',
        'rate' => '8.3750'
    ],
];
foreach ($data as $row) {
    $this->getConnection()->insertForce($this->getTable('tax_calculation_rate'), $row);
}
