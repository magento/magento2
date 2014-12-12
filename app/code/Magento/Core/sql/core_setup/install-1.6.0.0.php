<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'core_resource'
 */
$table = $installer->getConnection()->newTable(
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
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_config_data'
 */
$table = $installer->getConnection()->newTable(
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
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_layout_update'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('core_layout_update')
)->addColumn(
    'layout_update_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Layout Update Id'
)->addColumn(
    'handle',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Handle'
)->addColumn(
    'xml',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Xml'
)->addColumn(
    'sort_order',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'default' => '0'],
    'Sort Order'
)->addIndex(
    $installer->getIdxName('core_layout_update', ['handle']),
    ['handle']
)->setComment(
    'Layout Updates'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_layout_link'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('core_layout_link')
)->addColumn(
    'layout_link_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Link Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Store Id'
)->addColumn(
    'area',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    [],
    'Area'
)->addColumn(
    'package',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    [],
    'Package'
)->addColumn(
    'theme',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    [],
    'Theme'
)->addColumn(
    'layout_update_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Layout Update Id'
)->addIndex(
    $installer->getIdxName(
        'core_layout_link',
        ['store_id', 'package', 'theme', 'layout_update_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['store_id', 'package', 'theme', 'layout_update_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('core_layout_link', ['layout_update_id']),
    ['layout_update_id']
)->addForeignKey(
    $installer->getFkName('core_layout_link', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('core_layout_link', 'layout_update_id', 'core_layout_update', 'layout_update_id'),
    'layout_update_id',
    $installer->getTable('core_layout_update'),
    'layout_update_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Layout Link'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_session'
 */
$table = $installer->getConnection()->newTable(
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
$installer->getConnection()->createTable($table);

/**
 * Create table 'design_change'
 */
$table = $installer->getConnection()->newTable(
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
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_variable'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('core_variable')
)->addColumn(
    'variable_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Variable Id'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Variable Code'
)->addColumn(
    'name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Variable Name'
)->addIndex(
    $installer->getIdxName(
        'core_variable',
        ['code'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['code'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->setComment(
    'Variables'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_variable_value'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('core_variable_value')
)->addColumn(
    'value_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Variable Value Id'
)->addColumn(
    'variable_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Variable Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Store Id'
)->addColumn(
    'plain_value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Plain Text Value'
)->addColumn(
    'html_value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Html Value'
)->addIndex(
    $installer->getIdxName(
        'core_variable_value',
        ['variable_id', 'store_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['variable_id', 'store_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('core_variable_value', ['store_id']),
    ['store_id']
)->addForeignKey(
    $installer->getFkName('core_variable_value', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('core_variable_value', 'variable_id', 'core_variable', 'variable_id'),
    'variable_id',
    $installer->getTable('core_variable'),
    'variable_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Variable Value'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_cache'
 */
$table = $installer->getConnection()->newTable(
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
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_cache_tag'
 */
$table = $installer->getConnection()->newTable(
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
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_cache_option'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('core_cache_option')
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => false, 'primary' => true],
    'Code'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    [],
    'Value'
)->setComment(
    'Cache Options'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'core_flag'
 */
$table = $installer->getConnection()->newTable(
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
$installer->getConnection()->createTable($table);

$installer->endSetup();
