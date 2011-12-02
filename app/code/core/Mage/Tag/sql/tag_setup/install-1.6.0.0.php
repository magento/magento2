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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'tag'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tag'))
    ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Tag Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Status')
    ->addColumn('first_customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'First Customer Id')
    ->addColumn('first_store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'First Store Id')
    ->addForeignKey($installer->getFkName('tag', 'first_customer_id', 'customer_entity', 'entity_id'),
        'first_customer_id', $installer->getTable('customer_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey($installer->getFkName('tag', 'first_store_id', 'core_store', 'store_id'),
        'first_store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setComment('Tag');
$installer->getConnection()->createTable($table);

/**
 * Create table 'tag_relation'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tag_relation'))
    ->addColumn('tag_relation_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Tag Relation Id')
    ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Tag Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Product Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Store Id')
    ->addColumn('active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Active')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addIndex($installer->getIdxName('tag_relation', array('tag_id', 'customer_id', 'product_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('tag_id', 'customer_id', 'product_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('tag_relation', array('product_id')),
        array('product_id'))
    ->addIndex($installer->getIdxName('tag_relation', array('tag_id')),
        array('tag_id'))
    ->addIndex($installer->getIdxName('tag_relation', array('customer_id')),
        array('customer_id'))
    ->addIndex($installer->getIdxName('tag_relation', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('tag_relation', 'customer_id', 'customer_entity', 'entity_id'),
        'customer_id', $installer->getTable('customer_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tag_relation', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tag_relation', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tag_relation', 'tag_id', 'tag', 'tag_id'),
        'tag_id', $installer->getTable('tag'), 'tag_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Tag Relation');
$installer->getConnection()->createTable($table);

/**
 * Create table 'tag_summary'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tag_summary'))
    ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Tag Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Store Id')
    ->addColumn('customers', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Customers')
    ->addColumn('products', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Products')
    ->addColumn('uses', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Uses')
    ->addColumn('historical_uses', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Historical Uses')
    ->addColumn('popularity', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Popularity')
    ->addColumn('base_popularity', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Base Popularity')
    ->addIndex($installer->getIdxName('tag_summary', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('tag_summary', array('tag_id')),
        array('tag_id'))
    ->addForeignKey($installer->getFkName('tag_summary', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tag_summary', 'tag_id', 'tag', 'tag_id'),
        'tag_id', $installer->getTable('tag'), 'tag_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Tag Summary');
$installer->getConnection()->createTable($table);

/**
 * Create table 'tag_properties'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('tag_properties'))
    ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Tag Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Store Id')
    ->addColumn('base_popularity', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Base Popularity')
    ->addIndex($installer->getIdxName('tag_properties', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('tag_properties', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('tag_properties', 'tag_id', 'tag', 'tag_id'),
        'tag_id', $installer->getTable('tag'), 'tag_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Tag Properties');
$installer->getConnection()->createTable($table);


$installer->endSetup();
