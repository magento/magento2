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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/** @var $installer Mage_Tax_Model_Resource_Setup */
$installer = $this;
//
/**
 * Create table 'tax/class'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tax_class'))
    ->addColumn('class_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Class Id')
    ->addColumn('class_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false
        ), 'Class Name')
    ->addColumn('class_type', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
        'nullable'  => false,
        'default'   => Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER,
        ), 'Class Type')
    ->setComment('Tax Class');
$installer->getConnection()->createTable($table);

/**
 * Create table 'tax/calculation_rule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tax_calculation_rule'))
    ->addColumn('tax_calculation_rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Tax Calculation Rule Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Code')
    ->addColumn('priority', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Priority')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Position')
    ->addIndex($installer->getIdxName('tax_calculation_rule', array('priority', 'position', 'tax_calculation_rule_id')),
        array('priority', 'position', 'tax_calculation_rule_id'))
    ->addIndex($installer->getIdxName('tax_calculation_rule', array('code')),
        array('code'))
    ->setComment('Tax Calculation Rule');
$installer->getConnection()->createTable($table);

/**
 * Create table 'tax/calculation_rate'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tax_calculation_rate'))
    ->addColumn('tax_calculation_rate_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Tax Calculation Rate Id')
    ->addColumn('tax_country_id', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
        'nullable'  => false,
        ), 'Tax Country Id')
    ->addColumn('tax_region_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Tax Region Id')
    ->addColumn('tax_postcode', Varien_Db_Ddl_Table::TYPE_TEXT, 21, array(
        ), 'Tax Postcode')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Code')
    ->addColumn('rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        ), 'Rate')
    ->addColumn('zip_is_range', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Zip Is Range')
    ->addColumn('zip_from', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Zip From')
    ->addColumn('zip_to', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Zip To')
    ->addIndex($installer->getIdxName('tax_calculation_rate', array('tax_country_id', 'tax_region_id', 'tax_postcode')),
        array('tax_country_id', 'tax_region_id', 'tax_postcode'))
    ->addIndex($installer->getIdxName('tax_calculation_rate', array('code')),
        array('code'))
    ->addIndex($installer->getIdxName('tax_calculation_rate', array('tax_calculation_rate_id', 'tax_country_id', 'tax_region_id', 'zip_is_range', 'tax_postcode')),
        array('tax_calculation_rate_id', 'tax_country_id', 'tax_region_id', 'zip_is_range', 'tax_postcode'))
    ->setComment('Tax Calculation Rate');
$installer->getConnection()->createTable($table);

/**
 * Create table 'tax/calculation'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tax_calculation'))
    ->addColumn('tax_calculation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Tax Calculation Id')
    ->addColumn('tax_calculation_rate_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Tax Calculation Rate Id')
    ->addColumn('tax_calculation_rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Tax Calculation Rule Id')
    ->addColumn('customer_tax_class_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        ), 'Customer Tax Class Id')
    ->addColumn('product_tax_class_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        ), 'Product Tax Class Id')
    ->addIndex($installer->getIdxName('tax_calculation', array('tax_calculation_rule_id')),
        array('tax_calculation_rule_id'))
    ->addIndex($installer->getIdxName('tax_calculation', array('tax_calculation_rate_id')),
        array('tax_calculation_rate_id'))
    ->addIndex($installer->getIdxName('tax_calculation', array('customer_tax_class_id')),
        array('customer_tax_class_id'))
    ->addIndex($installer->getIdxName('tax_calculation', array('product_tax_class_id')),
        array('product_tax_class_id'))
    ->addIndex($installer->getIdxName('tax_calculation', array('tax_calculation_rate_id', 'customer_tax_class_id', 'product_tax_class_id')),
        array('tax_calculation_rate_id', 'customer_tax_class_id', 'product_tax_class_id'))
    ->addForeignKey($installer->getFkName('tax_calculation', 'product_tax_class_id', 'tax_class', 'class_id'),
        'product_tax_class_id', $installer->getTable('tax_class'), 'class_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tax_calculation', 'customer_tax_class_id', 'tax_class', 'class_id'),
        'customer_tax_class_id', $installer->getTable('tax_class'), 'class_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tax_calculation', 'tax_calculation_rate_id', 'tax_calculation_rate', 'tax_calculation_rate_id'),
        'tax_calculation_rate_id', $installer->getTable('tax_calculation_rate'), 'tax_calculation_rate_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tax_calculation', 'tax_calculation_rule_id', 'tax_calculation_rule', 'tax_calculation_rule_id'),
        'tax_calculation_rule_id', $installer->getTable('tax_calculation_rule'), 'tax_calculation_rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Tax Calculation');
$installer->getConnection()->createTable($table);

/**
 * Create table 'tax/calculation_rate_title'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tax_calculation_rate_title'))
    ->addColumn('tax_calculation_rate_title_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Tax Calculation Rate Title Id')
    ->addColumn('tax_calculation_rate_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Tax Calculation Rate Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Store Id')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Value')
    ->addIndex($installer->getIdxName('tax_calculation_rate_title', array('tax_calculation_rate_id', 'store_id')),
        array('tax_calculation_rate_id', 'store_id'))
    ->addIndex($installer->getIdxName('tax_calculation_rate_title', array('tax_calculation_rate_id')),
        array('tax_calculation_rate_id'))
    ->addIndex($installer->getIdxName('tax_calculation_rate_title', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('tax_calculation_rate_title', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tax_calculation_rate_title', 'tax_calculation_rate_id', 'tax_calculation_rate', 'tax_calculation_rate_id'),
        'tax_calculation_rate_id', $installer->getTable('tax_calculation_rate'), 'tax_calculation_rate_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Tax Calculation Rate Title');
$installer->getConnection()->createTable($table);

/**
 * Create table 'tax/order_aggregated_created'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tax_order_aggregated_created'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => true,
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Code')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable'  => false,
        ), 'Order Status')
    ->addColumn('percent', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Percent')
    ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Orders Count')
    ->addColumn('tax_base_amount_sum', Varien_Db_Ddl_Table::TYPE_FLOAT, null, array(
        ), 'Tax Base Amount Sum')
    ->addIndex($installer->getIdxName('tax_order_aggregated_created', array('period', 'store_id', 'code', 'percent', 'order_status'), true),
        array('period', 'store_id', 'code', 'percent', 'order_status'), array('type' => 'unique'))
    ->addIndex($installer->getIdxName('tax_order_aggregated_created', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('tax_order_aggregated_created', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Tax Order Aggregation');
$installer->getConnection()->createTable($table);

/**
 * Add tax_class_id attribute to the 'eav_attribute' table
 */
$catalogInstaller = Mage::getResourceModel(
    'Mage_Catalog_Model_Resource_Setup',
    array('resourceName' => 'catalog_setup')
);
$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'tax_class_id', array(
    'group'                      => 'Prices',
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Tax Class',
    'input'                      => 'select',
    'class'                      => '',
    'source'                     => 'Mage_Tax_Model_Class_Source_Product',
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible'                    => true,
    'required'                   => true,
    'user_defined'               => false,
    'default'                    => '',
    'searchable'                 => true,
    'filterable'                 => false,
    'comparable'                 => false,
    'visible_on_front'           => false,
    'visible_in_advanced_search' => true,
    'used_in_product_listing'    => true,
    'unique'                     => false,
    'apply_to'                   => 'simple,configurable,virtual,downloadable,bundle'
));

$installer->endSetup();
