<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

/**
 * Create table 'core_theme_files'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('core_theme_files')
)->addColumn(
    'theme_files_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Theme files identifier'
)->addColumn(
    'theme_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'unsigned' => true],
    'Theme Id'
)->addColumn(
    'file_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'File Name'
)->addColumn(
    'file_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => false],
    'File Type'
)->addColumn(
    'content',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
    ['nullable' => false],
    'File Content'
)->addColumn(
    'order',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => 0],
    'Order'
)->addForeignKey(
    $installer->getFkName('core_theme_files', 'theme_id', 'core_theme', 'theme_id'),
    'theme_id',
    $installer->getTable('core_theme'),
    'theme_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Core theme files'
);

$installer->getConnection()->createTable($table);

$installer->endSetup();
