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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_XmlConnect_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'xmlconnect_application'
 */
$appTableName = $installer->getTable('xmlconnect_application');
$table = $installer->getConnection()
    ->newTable($appTableName)
    ->addColumn('application_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Application Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'Application Name')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable'  => false,
        ), 'Application Code')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable'  => false,
        ), 'Device Type')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('active_from', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Active From')
    ->addColumn('active_to', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Active To')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ), 'Status')
    ->addColumn('browsing_mode', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'default'   => '0',
        ), 'Browsing Mode')
    ->addIndex(
        $installer->getIdxName($appTableName, array('code'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('code'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName($appTableName, 'store_id', $installer->getTable('core_store'), 'store_id'),
        'store_id',
        $installer->getTable('core_store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_SET_NULL
    )
    ->setComment('Xmlconnect Application');
$installer->getConnection()->createTable($table);

/**
 * Create table 'xmlconnect_config_data'
 */
$configTableName = $installer->getTable('xmlconnect_config_data');
$configTable = $installer->getConnection()
    ->newTable($configTableName)
    ->addColumn('application_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Application Id')
    ->addColumn('category', Varien_Db_Ddl_Table::TYPE_TEXT, 60, array(
            'nullable'  => false,
            'default'  => 'default',
        ), 'Category')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_TEXT, 250, array(
            'nullable'  => false,
        ), 'Path')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
            'nullable'  => false,
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            $configTableName,
            array('application_id', 'category', 'path'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('application_id', 'category', 'path'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName($configTableName, 'application_id', $appTableName, 'application_id'),
        'application_id',
        $appTableName,
        'application_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Xmlconnect Configuration Data');
$installer->getConnection()->createTable($configTable);

/**
 * Create table 'xmlconnect_history'
 */
$historyTableName = $installer->getTable('xmlconnect_history');
$historyTable = $installer->getConnection()
    ->newTable($historyTableName)
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'History Id')
    ->addColumn('application_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Application Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('params', Varien_Db_Ddl_Table::TYPE_BLOB, '64K', array(
        ), 'Params')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 200, array(
            'nullable'  => false,
        ), 'Title')
    ->addColumn('activation_key', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'Activation Key')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'Application Name')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
            'nullable'  => false,
        ), 'Application Code')
    ->addForeignKey(
        $installer->getFkName($historyTableName, 'application_id', $appTableName, 'application_id'),
        'application_id',
        $appTableName,
        'application_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Xmlconnect History');
$installer->getConnection()->createTable($historyTable);

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_category', 'thumbnail', array(
    'type'          => 'varchar',
    'label'         => 'Thumbnail Image',
    'input'         => 'image',
    'backend'       => 'Mage_Catalog_Model_Category_Attribute_Backend_Image',
    'required'      => false,
    'sort_order'    => 4,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group'         => 'General Information'
));

/**
 * Create table 'xmlconnect_notification_template'
 */
$templateTableName = $installer->getTable('xmlconnect_notification_template');
$templateTable = $installer->getConnection()
    ->newTable($templateTableName)
    ->addColumn('template_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Template Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'Template Name')
    ->addColumn('push_title', Varien_Db_Ddl_Table::TYPE_TEXT, 140, array(
            'nullable'  => false,
        ), 'Push Notification Title')
    ->addColumn('message_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ), 'Message Title')
    ->addColumn('content', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
            'nullable'  => false,
        ), 'Message Content')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('modified_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Modified At')
    ->addColumn('application_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Application Id')
    ->addForeignKey(
        $installer->getFkName($templateTableName, 'application_id', $appTableName, 'application_id'),
        'application_id',
        $appTableName,
        'application_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Xmlconnect Notification Template');
$installer->getConnection()->createTable($templateTable);


/**
 * Create table 'xmlconnect_queue'
 */
$queueTableName = $installer->getTable('xmlconnect_queue');
$queueTable = $installer->getConnection()
    ->newTable($queueTableName)
    ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Queue Id')
    ->addColumn('create_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('exec_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Scheduled Execution Time')
    ->addColumn('template_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ), 'Template Id')
    ->addColumn('push_title', Varien_Db_Ddl_Table::TYPE_TEXT, 140, array(
            'nullable'  => false,
        ), 'Push Notification Title')
    ->addColumn('message_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'default'   => ''
        ), 'Message Title')
    ->addColumn('content', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
            'default'   => ''
        ), 'Message Content')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => 0
        ), 'Status')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 12, array(
            'nullable'  => false,
        ), 'Type of Notification')
    ->addForeignKey(
        $installer->getFkName($queueTableName, 'template_id', $templateTableName, 'template_id'),
        'template_id',
        $templateTableName,
        'template_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Xmlconnect Notification Queue');
$installer->getConnection()->createTable($queueTable);

$installer->endSetup();
