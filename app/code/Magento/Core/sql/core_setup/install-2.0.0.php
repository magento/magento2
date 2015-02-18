<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

/* @var $connection \Magento\Framework\DB\Adapter\AdapterInterface */
$connection = $installer->getConnection();

$installer->startSetup();

/**
 * Create table 'core_resource'
 */
$table = $connection->newTable(
    $installer->getTable('core_resource')
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    ['nullable' => false, 'primary' => true],
    'Resource Code'
)->addColumn(
    'version',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Resource Version'
)->addColumn(
    'data_version',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Data Version'
)->setComment(
    'Resources'
);
$connection->createTable($table);

/**
 * Create table 'core_config_data'
 */
$table = $connection->newTable(
    $installer->getTable('core_config_data')
)->addColumn(
    'config_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Config Id'
)->addColumn(
    'scope',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    8,
    ['nullable' => false, 'default' => 'default'],
    'Config Scope'
)->addColumn(
    'scope_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Config Scope Id'
)->addColumn(
    'path',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false, 'default' => 'general'],
    'Config Path'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Config Value'
)->addIndex(
    $installer->getIdxName(
        'core_config_data',
        ['scope', 'scope_id', 'path'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['scope', 'scope_id', 'path'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->setComment(
    'Config Data'
);
$connection->createTable($table);

/**
 * Create table 'core_session'
 */
$table = $connection->newTable(
    $installer->getTable('core_session')
)->addColumn(
    'session_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false, 'primary' => true],
    'Session Id'
)->addColumn(
    'session_expires',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Date of Session Expiration'
)->addColumn(
    'session_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
    '2M',
    ['nullable' => false],
    'Session Data'
)->setComment(
    'Database Sessions Storage'
);
$connection->createTable($table);

/**
 * Create table 'design_change'
 */
$table = $connection->newTable(
    $installer->getTable('design_change')
)->addColumn(
    'design_change_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'Design Change Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Store Id'
)->addColumn(
    'design',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Design'
)->addColumn(
    'date_from',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    [],
    'First Date of Design Activity'
)->addColumn(
    'date_to',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    [],
    'Last Date of Design Activity'
)->addIndex(
    $installer->getIdxName('design_change', ['store_id']),
    ['store_id']
)->addForeignKey(
    $installer->getFkName('design_change', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Design Changes'
);
$connection->createTable($table);

/**
 * Create table 'core_cache'
 */
$table = $connection->newTable(
    $installer->getTable('core_cache')
)->addColumn(
    'id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    ['nullable' => false, 'primary' => true],
    'Cache Id'
)->addColumn(
    'data',
    \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
    '2M',
    [],
    'Cache Data'
)->addColumn(
    'create_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Cache Creation Time'
)->addColumn(
    'update_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Time of Cache Updating'
)->addColumn(
    'expire_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Cache Expiration Time'
)->addIndex(
    $installer->getIdxName('core_cache', ['expire_time']),
    ['expire_time']
)->setComment(
    'Caches'
);
$connection->createTable($table);

/**
 * Create table 'core_cache_tag'
 */
$table = $connection->newTable(
    $installer->getTable('core_cache_tag')
)->addColumn(
    'tag',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    100,
    ['nullable' => false, 'primary' => true],
    'Tag'
)->addColumn(
    'cache_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    200,
    ['nullable' => false, 'primary' => true],
    'Cache Id'
)->addIndex(
    $installer->getIdxName('core_cache_tag', ['cache_id']),
    ['cache_id']
)->setComment(
    'Tag Caches'
);
$connection->createTable($table);

/**
 * Create table 'core_flag'
 */
$table = $connection->newTable(
    $installer->getTable('core_flag')
)->addColumn(
    'flag_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Flag Id'
)->addColumn(
    'flag_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'Flag Code'
)->addColumn(
    'state',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Flag State'
)->addColumn(
    'flag_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Flag Data'
)->addColumn(
    'last_update',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
    'Date of Last Flag Update'
)->addIndex(
    $installer->getIdxName('core_flag', ['last_update']),
    ['last_update']
)->setComment(
    'Flag'
);
$connection->createTable($table);

/**
 * Drop Foreign Key on core_cache_tag.cache_id
 */
$connection->dropForeignKey(
    $installer->getTable('core_cache_tag'),
    $installer->getFkName('core_cache_tag', 'cache_id', 'core_cache', 'id')
);

$installer->endSetup();
