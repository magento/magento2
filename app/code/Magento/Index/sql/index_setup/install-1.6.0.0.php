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

$installer = $this;
/* @var $installer \Magento\Index\Model\Resource\Setup */

$installer->startSetup();

/**
 * Create table 'index_event'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('index_event')
)->addColumn(
    'event_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Event Id'
)->addColumn(
    'type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    array('nullable' => false),
    'Type'
)->addColumn(
    'entity',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    array('nullable' => false),
    'Entity'
)->addColumn(
    'entity_pk',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array(),
    'Entity Primary Key'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Creation Time'
)->addColumn(
    'old_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '2M',
    array(),
    'Old Data'
)->addColumn(
    'new_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '2M',
    array(),
    'New Data'
)->addIndex(
    $installer->getIdxName(
        'index_event',
        array('type', 'entity', 'entity_pk'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('type', 'entity', 'entity_pk'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->setComment(
    'Index Event'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'index_process'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('index_process')
)->addColumn(
    'process_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Process Id'
)->addColumn(
    'indexer_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array('nullable' => false),
    'Indexer Code'
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    15,
    array('nullable' => false, 'default' => 'pending'),
    'Status'
)->addColumn(
    'started_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Started At'
)->addColumn(
    'ended_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Ended At'
)->addColumn(
    'mode',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    9,
    array('nullable' => false, 'default' => 'real_time'),
    'Mode'
)->addIndex(
    $installer->getIdxName(
        'index_process',
        array('indexer_code'),
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    array('indexer_code'),
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->setComment(
    'Index Process'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'index_process_event'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('index_process_event')
)->addColumn(
    'process_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Process Id'
)->addColumn(
    'event_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true),
    'Event Id'
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    7,
    array('nullable' => false, 'default' => 'new'),
    'Status'
)->addIndex(
    $installer->getIdxName('index_process_event', array('event_id')),
    array('event_id')
)->addForeignKey(
    $installer->getFkName('index_process_event', 'event_id', 'index_event', 'event_id'),
    'event_id',
    $installer->getTable('index_event'),
    'event_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('index_process_event', 'process_id', 'index_process', 'process_id'),
    'process_id',
    $installer->getTable('index_process'),
    'process_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Index Process Event'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
