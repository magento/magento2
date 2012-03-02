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
 * @package     Mage_CatalogRule
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

/**
 * Create table 'catalogrule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalogrule'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('from_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'From Date')
    ->addColumn('to_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'To Date')
    ->addColumn('customer_group_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Customer Group Ids')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Active')
    ->addColumn('conditions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Conditions Serialized')
    ->addColumn('actions_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Actions Serialized')
    ->addColumn('stop_rules_processing', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '1',
        ), 'Stop Rules Processing')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Sort Order')
    ->addColumn('simple_action', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Simple Action')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
        'nullable'  => false,
        'default'   => 0.0000,
        ), 'Discount Amount')
    ->addColumn('website_ids', Varien_Db_Ddl_Table::TYPE_TEXT, 4000, array(
        ), 'Website Ids')
    ->addIndex($installer->getIdxName('catalogrule', array('is_active', 'sort_order', 'to_date', 'from_date')),
        array('is_active', 'sort_order', 'to_date', 'from_date'))

    ->setComment('CatalogRule');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalogrule_product'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalogrule_product'))
    ->addColumn('rule_product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Product Id')
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Rule Id')
    ->addColumn('from_time', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'From Time')
    ->addColumn('to_time', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'To time')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Customer Group Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Product Id')
    ->addColumn('action_operator', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
        'default'   => 'to_fixed',
        ), 'Action Operator')
    ->addColumn('action_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Action Amount')
    ->addColumn('action_stop', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Action Stop')
    ->addColumn('sort_order', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Sort Order')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Website Id')
    ->addIndex($installer->getIdxName('catalogrule_product', array('rule_id', 'from_time', 'to_time', 'website_id', 'customer_group_id', 'product_id', 'sort_order'), true),
        array('rule_id', 'from_time', 'to_time', 'website_id', 'customer_group_id', 'product_id', 'sort_order'), array('type' => 'unique'))

    ->addIndex($installer->getIdxName('catalogrule_product', array('rule_id')),
        array('rule_id'))
    ->addIndex($installer->getIdxName('catalogrule_product', array('customer_group_id')),
        array('customer_group_id'))
    ->addIndex($installer->getIdxName('catalogrule_product', array('website_id')),
        array('website_id'))
    ->addIndex($installer->getIdxName('catalogrule_product', array('from_time')),
        array('from_time'))
    ->addIndex($installer->getIdxName('catalogrule_product', array('to_time')),
        array('to_time'))
    ->addIndex($installer->getIdxName('catalogrule_product', array('product_id')),
        array('product_id'))

    ->addForeignKey($installer->getFkName('catalogrule_product', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey($installer->getFkName('catalogrule_product', 'customer_group_id', 'customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey($installer->getFkName('catalogrule_product', 'rule_id', 'catalogrule', 'rule_id'),
        'rule_id', $installer->getTable('catalogrule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey($installer->getFkName('catalogrule_product', 'website_id', 'core_website', 'website_id'),
        'website_id', $installer->getTable('core_website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->setComment('CatalogRule Product');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalogrule_product_price'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalogrule_product_price'))
    ->addColumn('rule_product_price_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rule Product PriceId')
    ->addColumn('rule_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => false,
        ), 'Rule Date')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Customer Group Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Product Id')
    ->addColumn('rule_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, array(12,4), array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Rule Price')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Website Id')
    ->addColumn('latest_start_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Latest StartDate')
    ->addColumn('earliest_end_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Earliest EndDate')

    ->addIndex($installer->getIdxName('catalogrule_product_price', array('rule_date', 'website_id', 'customer_group_id', 'product_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('rule_date', 'website_id', 'customer_group_id', 'product_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('catalogrule_product_price', array('customer_group_id')),
        array('customer_group_id'))
    ->addIndex($installer->getIdxName('catalogrule_product_price', array('website_id')),
        array('website_id'))
    ->addIndex($installer->getIdxName('catalogrule_product_price', array('product_id')),
        array('product_id'))

    ->addForeignKey($installer->getFkName('catalogrule_product_price', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey($installer->getFkName('catalogrule_product_price', 'customer_group_id', 'customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey($installer->getFkName('catalogrule_product_price', 'website_id', 'core_website', 'website_id'),
        'website_id', $installer->getTable('core_website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->setComment('CatalogRule Product Price');
$installer->getConnection()->createTable($table);

/**
 * Create table 'catalogrule_affected_product'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalogrule_affected_product'))
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Product Id')
    ->setComment('CatalogRule Affected Product');

$installer->getConnection()->createTable($table);

/**
 * Create table 'catalogrule_group_website'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('catalogrule_group_website'))
    ->addColumn('rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Rule Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Customer Group Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Website Id')
    ->addIndex($installer->getIdxName('catalogrule_group_website', array('rule_id')),
        array('rule_id'))
    ->addIndex($installer->getIdxName('catalogrule_group_website', array('customer_group_id')),
        array('customer_group_id'))
    ->addIndex($installer->getIdxName('catalogrule_group_website', array('website_id')),
        array('website_id'))

    ->addForeignKey($installer->getFkName('catalogrule_group_website', 'customer_group_id', 'customer_group', 'customer_group_id'),
        'customer_group_id', $installer->getTable('customer_group'), 'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey($installer->getFkName('catalogrule_group_website', 'rule_id', 'catalogrule', 'rule_id'),
        'rule_id', $installer->getTable('catalogrule'), 'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)

    ->addForeignKey($installer->getFkName('catalogrule_group_website', 'website_id', 'core_website', 'website_id'),
        'website_id', $installer->getTable('core_website'), 'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('CatalogRule Group Website');

$installer->getConnection()->createTable($table);

$installer->endSetup();
