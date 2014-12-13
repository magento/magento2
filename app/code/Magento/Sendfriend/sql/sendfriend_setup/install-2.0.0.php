<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()->newTable(
    $installer->getTable('sendfriend_log')
)->addColumn(
    'log_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Log ID'
)->addColumn(
    'ip',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    '20',
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Customer IP address'
)->addColumn(
    'time',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Log time'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Website ID'
)->addIndex(
    $installer->getIdxName('sendfriend_log', 'ip'),
    'ip'
)->addIndex(
    $installer->getIdxName('sendfriend_log', 'time'),
    'time'
)->setComment(
    'Send to friend function log storage table'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
