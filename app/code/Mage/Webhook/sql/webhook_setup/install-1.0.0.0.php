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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var Mage_Core_Model_Resource_Setup $this */
$this->startSetup();

/* @var $connection Varien_Db_Adapter_Interface */
$connection = $this->getConnection();

/**
 * Create new table 'webhook_subscriber'
 */
$subscriberTable = $this->getConnection()
    ->newTable($this->getTable('webhook_subscriber'))
    ->addColumn('subscriber_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
        array('identity'  => true, 'unsigned'  => true, 'nullable'  => false, 'primary'   => true),
        'Subscriber Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array('nullable' =>false),
        'Subscriber Name')
    ->addColumn('endpoint_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array('nullable' =>false),
        'Endpoint URL')
    ->addColumn('authentication_type', Varien_Db_Ddl_Table::TYPE_TEXT, 40,
        array('nullable' =>false),
        'Authentication Type')
    ->addColumn('registration_mechanism', Varien_Db_Ddl_Table::TYPE_TEXT, 40,
        array('nullable' =>false),
        'Registration Mechanism')
    ->addColumn('encoding', Varien_Db_Ddl_Table::TYPE_TEXT, 40,
        array('nullable' =>false), 'Encoding')
    ->addColumn('format', Varien_Db_Ddl_Table::TYPE_TEXT, 40,
        array('nullable' =>false),
        'Data Format')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array('unsigned' => true, 'nullable' => false, 'default'  => 0),
        'Status')
    ->addColumn('version', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(),
        'Extension Version')
    ->addColumn('extension_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(),
        'Extension Id')
    ->addColumn('api_user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true, 'nullable' => true),
        'Webapi User Id')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(),
        'Updated At')
    ->addIndex(
        $this->getIdxName('webhook_subscriber', array('subscriber_id', 'api_user_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('subscriber_id', 'api_user_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
        $this->getFkName('webhook_subscriber', 'api_user_id', 'webapi_user', 'user_id'),
        'api_user_id',
        $this->getTable('webapi_user'),
        'user_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setOption('collate', null)
    ->setOption('comment', 'Subscriber');
$this->getConnection()->createTable($subscriberTable);

/**
 * Create table 'webhook_subscriber_hook'
 */
$hookTable = $this->getConnection()->newTable($this->getTable('webhook_subscriber_hook'))
    ->addColumn('hook_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Hook Id')
    ->addColumn('subscriber_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Subscriber Id')
    ->addColumn('topic', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Hook Topic')
    ->addIndex(
        $this->getIdxName('webhook_subscriber_hook', array('topic')),
        array('topic'))
    ->addForeignKey(
        'FK_WEBHOOK_SUBSCRIBER_SUBSCRIBER_ID',
        'subscriber_id',
        $this->getTable('webhook_subscriber'),
        'subscriber_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setOption('collate', null)
    ->setOption('comment', 'Webhook');
$this->getConnection()->createTable($hookTable);

/**
 * Create table 'webhook_event'
 */
$eventTable = $this->getConnection()->newTable($this->getTable('webhook_event'))
    ->addColumn('event_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
        array('identity'  => true, 'unsigned'  => true, 'nullable'  => false, 'primary'   => true),
        'Event Id')
    ->addColumn('topic', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array('nullable' => false), 'Hook Topic')
    ->addColumn('format', Varien_Db_Ddl_Table::TYPE_TEXT, '255', array(),
        'Format')
    ->addColumn('body_data', Varien_Db_Ddl_Table::TYPE_VARBINARY, '4M',
        array('nullable' => false),
        'Serialized Data Array')
    ->addColumn('headers', Varien_Db_Ddl_Table::TYPE_TEXT, '16k', array(),
        'Headers')
    ->addColumn('options', Varien_Db_Ddl_Table::TYPE_TEXT, '16k', array(),
        'Options')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array('nullable' => false),
        'Status')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array('nullable' => false),
        'Updated At')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array('nullable' => false),
        'Created At')
    ->addIndex($this->getIdxName('webhook_event', array('status')), array('status'))
    ->setOption('collate', null)
    ->setOption('comment', 'Queued Event Data');
$this->getConnection()->createTable($eventTable);

/**
 * Create table 'webhook_dispatch_job'
 */
$dispatchJobTable = $connection->newTable($this->getTable('webhook_dispatch_job'))
    ->addColumn('dispatch_job_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
    'identity'  => true, 'unsigned'  => true, 'nullable'  => false, 'primary'   => true),
        'Dispatch Job Id')
    ->addColumn('event_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array('unsigned'  => true, 'nullable'  => false),
        'Event Id')
    ->addColumn('subscriber_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array('unsigned'  => true, 'nullable'  => false),
        'Subscriber Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array('nullable'  => false, 'default'  => '0'),
        'Status')
    ->addColumn('retry_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array('unsigned'  => true, 'nullable'  => false),
        'Retry Count')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array('default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE, 'nullable' => false),
        'Updated At')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array('default' => '0000-00-00 00:00:00', 'nullable' => false),
        'Created At')
    ->addColumn('retry_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null,
        array('default' => '0000-00-00 00:00:00', 'nullable' => false),
        'Retry At')
    ->addForeignKey(
        'FK_WEBHOOK_SERVICE_DISPATCHER_ID',
        'subscriber_id',
        $this->getTable('webhook_subscriber'),
        'subscriber_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        'FK_WEBHOOK_MESSAGE_DISPATCHER_ID',
        'event_id',
        $this->getTable('webhook_event'),
        'event_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setOption('collate', null)
    ->setOption('comment', 'Dispatch Jobs');
$this->getConnection()->createTable($dispatchJobTable);

$this->endSetup();

