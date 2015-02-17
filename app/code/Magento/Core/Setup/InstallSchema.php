<?php
/**
* Copyright © 2015 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

// @codingStandardsIgnoreFile

namespace Magento\Core\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaResourceInterface;

class InstallSchema implements InstallSchemaInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function install(SchemaResourceInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;
		
		/* @var $connection \Magento\Framework\DB\Adapter\AdapterInterface */
		$connection = $installer->getConnection();
		
		$installer->startSetup();
		
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
		 * Create table 'core_layout_update'
		 */
		$table = $connection->newTable(
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
		)->addColumn(
		    'updated_at',
		    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
		    null,
		    ['nullable' => true],
		    'Last Update Timestamp'
		)->addIndex(
		    $installer->getIdxName('core_layout_update', ['handle']),
		    ['handle']
		)->setComment(
		    'Layout Updates'
		);
		$connection->createTable($table);
		
		/**
		 * Create table 'core_layout_link'
		 */
		$table = $connection->newTable(
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
		    'theme_id',
		    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
		    null,
		    ['unsigned' => true, 'nullable' => false],
		    'Theme id'
		)->addColumn(
		    'layout_update_id',
		    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
		    null,
		    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
		    'Layout Update Id'
		)->addColumn(
		    'is_temporary',
		    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
		    null,
		    ['nullable' => false, 'default' => '0'],
		    'Defines whether Layout Update is Temporary'
		)->addIndex(
		    $installer->getIdxName('core_layout_link', ['layout_update_id']),
		    ['layout_update_id']
		)->addForeignKey(
		    $installer->getFkName('core_layout_link', 'layout_update_id', 'core_layout_update', 'layout_update_id'),
		    'layout_update_id',
		    $installer->getTable('core_layout_update'),
		    'layout_update_id',
		    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
		    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)->addIndex(
		    $installer->getIdxName(
		        'core_layout_link',
		        ['store_id', 'theme_id', 'layout_update_id', 'is_temporary'],
		        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
		    ),
		    ['store_id', 'theme_id', 'layout_update_id', 'is_temporary']
		)->addForeignKey(
		    $installer->getFkName('core_layout_link', 'store_id', 'store', 'store_id'),
		    'store_id',
		    $installer->getTable('store'),
		    'store_id',
		    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
		    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)->addForeignKey(
		    $installer->getFkName('core_layout_link', 'theme_id', 'theme', 'theme_id'),
		    'theme_id',
		    $installer->getTable('theme'),
		    'theme_id',
		    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
		    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)->setComment(
		    'Layout Link'
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
		 * Create table 'core_variable'
		 */
		$table = $connection->newTable(
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
		$connection->createTable($table);
		
		/**
		 * Create table 'core_variable_value'
		 */
		$table = $connection->newTable(
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
		
	}
}