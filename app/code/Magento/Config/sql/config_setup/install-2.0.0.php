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

$installer->endSetup();
