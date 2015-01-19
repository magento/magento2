<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;

$productTypes = [
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
    \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
    \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE,
];
$productTypes = join(',', $productTypes);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'msrp',
    [
        'group' => 'Advanced Pricing',
        'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
        'frontend' => '',
        'label' => 'Manufacturer\'s Suggested Retail Price',
        'type' => 'decimal',
        'input' => 'price',
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'apply_to' => $productTypes,
        'input_renderer' => 'Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type',
        'frontend_input_renderer' => 'Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type',
        'visible_on_front' => false,
        'used_in_product_listing' => true
    ]
);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'msrp_display_actual_price_type',
    [
        'group' => 'Advanced Pricing',
        'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Boolean',
        'frontend' => '',
        'label' => 'Display Actual Price',
        'input' => 'select',
        'source' => 'Magento\Msrp\Model\Product\Attribute\Source\Type\Price',
        'source_model' => 'Magento\Msrp\Model\Product\Attribute\Source\Type\Price',
        'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'default' => \Magento\Msrp\Model\Product\Attribute\Source\Type\Price::TYPE_USE_CONFIG,
        'default_value' => \Magento\Msrp\Model\Product\Attribute\Source\Type\Price::TYPE_USE_CONFIG,
        'apply_to' => $productTypes,
        'input_renderer' => 'Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type\Price',
        'frontend_input_renderer' => 'Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type\Price',
        'visible_on_front' => false,
        'used_in_product_listing' => true
    ]
);
