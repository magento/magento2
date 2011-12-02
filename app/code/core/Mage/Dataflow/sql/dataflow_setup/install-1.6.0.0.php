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
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'dataflow_session'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('dataflow_session'))
    ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Session Id')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'User Id')
    ->addColumn('created_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created Date')
    ->addColumn('file', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'File')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Type')
    ->addColumn('direction', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Direction')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Comment')
    ->setComment('Dataflow Session');
$installer->getConnection()->createTable($table);

/**
 * Create table 'dataflow_import_data'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('dataflow_import_data'))
    ->addColumn('import_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Import Id')
    ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Session Id')
    ->addColumn('serial_number', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Serial Number')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Value')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Status')
    ->addIndex($installer->getIdxName('dataflow_import_data', array('session_id')),
        array('session_id'))
    ->addForeignKey($installer->getFkName('dataflow_import_data', 'session_id', 'dataflow_session', 'session_id'),
        'session_id', $installer->getTable('dataflow_session'), 'session_id',
        Varien_Db_Ddl_Table::ACTION_NO_ACTION, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setComment('Dataflow Import Data');
$installer->getConnection()->createTable($table);

/**
 * Create table 'dataflow_profile'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('dataflow_profile'))
    ->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Profile Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addColumn('actions_xml', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Actions Xml')
    ->addColumn('gui_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Gui Data')
    ->addColumn('direction', Varien_Db_Ddl_Table::TYPE_TEXT, 6, array(
        ), 'Direction')
    ->addColumn('entity_type', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
        ), 'Entity Type')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store Id')
    ->addColumn('data_transfer', Varien_Db_Ddl_Table::TYPE_TEXT, 11, array(
        ), 'Data Transfer')
    ->setComment('Dataflow Profile');
$installer->getConnection()->createTable($table);

/**
 * Create table 'dataflow_profile_history'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('dataflow_profile_history'))
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'History Id')
    ->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Profile Id')
    ->addColumn('action_code', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
        ), 'Action Code')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'User Id')
    ->addColumn('performed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Performed At')
    ->addIndex($installer->getIdxName('dataflow_profile_history', array('profile_id')),
        array('profile_id'))
    ->addForeignKey($installer->getFkName('dataflow_profile_history', 'profile_id', 'dataflow_profile', 'profile_id'),
        'profile_id', $installer->getTable('dataflow_profile'), 'profile_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Dataflow Profile History');
$installer->getConnection()->createTable($table);

/**
 * Create table 'dataflow_batch'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('dataflow_batch'))
    ->addColumn('batch_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Batch Id')
    ->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Profile ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store Id')
    ->addColumn('adapter', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        ), 'Adapter')
    ->addColumn('params', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Parameters')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addIndex($installer->getIdxName('dataflow_batch', array('profile_id')),
        array('profile_id'))
    ->addIndex($installer->getIdxName('dataflow_batch', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('dataflow_batch', array('created_at')),
        array('created_at'))
    ->addForeignKey($installer->getFkName('dataflow_batch', 'profile_id', 'dataflow_profile', 'profile_id'),
        'profile_id', $installer->getTable('dataflow_profile'), 'profile_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey($installer->getFkName('dataflow_batch', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setComment('Dataflow Batch');
$installer->getConnection()->createTable($table);

/**
 * Create table 'dataflow_batch_export'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('dataflow_batch_export'))
    ->addColumn('batch_export_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Batch Export Id')
    ->addColumn('batch_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Batch Id')
    ->addColumn('batch_data', Varien_Db_Ddl_Table::TYPE_TEXT, '2G', array(
        ), 'Batch Data')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Status')
    ->addIndex($installer->getIdxName('dataflow_batch_export', array('batch_id')),
        array('batch_id'))
    ->addForeignKey($installer->getFkName('dataflow_batch_export', 'batch_id', 'dataflow_batch', 'batch_id'),
        'batch_id', $installer->getTable('dataflow_batch'), 'batch_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setComment('Dataflow Batch Export');
$installer->getConnection()->createTable($table);

/**
 * Create table 'dataflow_batch_import'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('dataflow_batch_import'))
    ->addColumn('batch_import_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Batch Import Id')
    ->addColumn('batch_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Batch Id')
    ->addColumn('batch_data', Varien_Db_Ddl_Table::TYPE_TEXT, '2G', array(
        ), 'Batch Data')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Status')
    ->addIndex($installer->getIdxName('dataflow_batch_import', array('batch_id')),
        array('batch_id'))
    ->addForeignKey($installer->getFkName('dataflow_batch_import', 'batch_id', 'dataflow_batch', 'batch_id'),
        'batch_id', $installer->getTable('dataflow_batch'), 'batch_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setComment('Dataflow Batch Import');
$installer->getConnection()->createTable($table);

$installer->endSetup();
