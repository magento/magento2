<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/** @var $installer \Magento\Downloadable\Model\Resource\Setup */
$installer = $this;
/**
 * Add attributes to the eav/attribute table
 */
$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'links_purchased_separately',
    array(
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
    )
);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'samples_title',
    array(
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
    )
);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'links_title',
    array(
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
    )
);

$installer->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'links_exist',
    array(
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
    )
);

$fieldList = array(
    'price',
    'special_price',
    'special_from_date',
    'special_to_date',
    'minimal_price',
    'cost',
    'tier_price'
);

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
