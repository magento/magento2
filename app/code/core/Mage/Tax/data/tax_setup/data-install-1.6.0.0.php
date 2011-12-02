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
 * @category    Mage
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/** @var $installer Mage_Tax_Model_Resource_Setup */
$installer = $this;
/**
 * install tax classes
 */
$data = array(
    array(
        'class_id'     => 2,
        'class_name'   => 'Taxable Goods',
        'class_type'   => Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT
    ),
    array(
        'class_id'     => 3,
        'class_name'   => 'Retail Customer',
        'class_type'   => Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER
    ),
    array(
        'class_id'     => 4,
        'class_name'   => 'Shipping',
        'class_type'   => Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT
    )
);
foreach ($data as $row) {
    $installer->getConnection()->insertForce($installer->getTable('tax_class'), $row);
}

/**
 * install tax calculation rule
 */
$data = array(
    'tax_calculation_rule_id'   => 1,
    'code'                      => 'Retail Customer-Taxable Goods-Rate 1',
    'priority'                  => 1,
    'position'                  => 1
);
$installer->getConnection()->insertForce($installer->getTable('tax_calculation_rule'), $data);

/**
 * install tax calculation rates
 */
$data = array(
    array(
        'tax_calculation_rate_id'   => 1,
        'tax_country_id'            => 'US',
        'tax_region_id'             => 12,
        'tax_postcode'              => '*',
        'code'                      => 'US-CA-*-Rate 1',
        'rate'                      => '8.2500'
    ),
    array(
        'tax_calculation_rate_id'   => 2,
        'tax_country_id'            => 'US',
        'tax_region_id'             => 43,
        'tax_postcode'              => '*',
        'code'                      => 'US-NY-*-Rate 1',
        'rate'                      => '8.3750'
    )
);
foreach ($data as $row) {
    $installer->getConnection()->insertForce($installer->getTable('tax_calculation_rate'), $row);
}

/**
 * install tax calculation
 */
$data = array(
    array(
        'tax_calculation_rate_id'   => 1,
        'tax_calculation_rule_id'   => 1,
        'customer_tax_class_id'     => 3,
        'product_tax_class_id'      => 2
    ),
    array(
        'tax_calculation_rate_id'   => 2,
        'tax_calculation_rule_id'   => 1,
        'customer_tax_class_id'     => 3,
        'product_tax_class_id'      => 2
    )
);
$installer->getConnection()->insertMultiple($installer->getTable('tax_calculation'), $data);

