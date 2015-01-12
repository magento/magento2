<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'persistent_session'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('persistent_session')
)->addColumn(
    'persistent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'primary' => true, 'nullable' => false, 'unsigned' => true],
    'Session id'
)->addColumn(
    'key',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    ['nullable' => false],
    'Unique cookie key'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Customer id'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Website ID'
)->addColumn(
    'info',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Session Data'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Updated At'
)->addIndex(
    $installer->getIdxName('persistent_session', ['key']),
    ['key'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('persistent_session', ['customer_id']),
    ['customer_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $installer->getIdxName('persistent_session', ['updated_at']),
    ['updated_at']
)->addForeignKey(
    $installer->getFkName('persistent_session', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('persistent_session', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Persistent Session'
);

$installer->getConnection()->createTable($table);

/**
 * Alter sales_quote table with is_persistent flag
 *
 */
$installer->getConnection()->addColumn(
    $installer->getTable('sales_quote'),
    'is_persistent',
    [
        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        'unsigned' => true,
        'default' => '0',
        'comment' => 'Is Quote Persistent'
    ]
);

$installer->endSetup();
