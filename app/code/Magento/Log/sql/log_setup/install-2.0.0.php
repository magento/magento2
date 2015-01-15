<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'log_customer'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_customer')
)->addColumn(
    'log_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Log ID'
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['unsigned' => true],
    'Visitor ID'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Customer ID'
)->addColumn(
    'login_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Login Time'
)->addColumn(
    'logout_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Logout Time'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Store ID'
)->addIndex(
    $installer->getIdxName('log_customer', ['visitor_id']),
    ['visitor_id']
)->setComment(
    'Log Customers Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'log_quote'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_quote')
)->addColumn(
    'quote_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
    'Quote ID'
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['unsigned' => true],
    'Visitor ID'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Creation Time'
)->addColumn(
    'deleted_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Deletion Time'
)->setComment(
    'Log Quotes Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'log_summary'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_summary')
)->addColumn(
    'summary_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Summary ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Store ID'
)->addColumn(
    'type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Type ID'
)->addColumn(
    'visitor_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Visitor Count'
)->addColumn(
    'customer_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Customer Count'
)->addColumn(
    'add_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Date'
)->setComment(
    'Log Summary Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'log_summary_type'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_summary_type')
)->addColumn(
    'type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Type ID'
)->addColumn(
    'type_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    ['nullable' => true, 'default' => null],
    'Type Code'
)->addColumn(
    'period',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Period'
)->addColumn(
    'period_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    6,
    ['nullable' => false, 'default' => 'MINUTE'],
    'Period Type'
)->setComment(
    'Log Summary Types Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'log_url'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_url')
)->addColumn(
    'url_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
    'URL ID'
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['unsigned' => true],
    'Visitor ID'
)->addColumn(
    'visit_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Visit Time'
)->addIndex(
    $installer->getIdxName('log_url', ['visitor_id']),
    ['visitor_id']
)->setComment(
    'Log URL Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'log_url_info'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_url_info')
)->addColumn(
    'url_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'URL ID'
)->addColumn(
    'url',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true, 'default' => null],
    'URL'
)->addColumn(
    'referer',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Referrer'
)->setComment(
    'Log URL Info Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'log_visitor'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_visitor')
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Visitor ID'
)->addColumn(
    'first_visit_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'First Visit Time'
)->addColumn(
    'last_visit_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Last Visit Time'
)->addColumn(
    'last_url_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Last URL ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Store ID'
)->setComment(
    'Log Visitors Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'log_visitor_info'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_visitor_info')
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
    'Visitor ID'
)->addColumn(
    'http_referer',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'HTTP Referrer'
)->addColumn(
    'http_user_agent',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'HTTP User-Agent'
)->addColumn(
    'http_accept_charset',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'HTTP Accept-Charset'
)->addColumn(
    'http_accept_language',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'HTTP Accept-Language'
)->addColumn(
    'server_addr',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    [],
    'Server Address'
)->addColumn(
    'remote_addr',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    [],
    'Remote Address'
)->setComment(
    'Log Visitor Info Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'log_visitor_online'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_visitor_online')
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Visitor ID'
)->addColumn(
    'visitor_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    1,
    ['nullable' => false],
    'Visitor Type'
)->addColumn(
    'remote_addr',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    ['nullable' => false],
    'Remote Address'
)->addColumn(
    'first_visit_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'First Visit Time'
)->addColumn(
    'last_visit_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Last Visit Time'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Customer ID'
)->addColumn(
    'last_url',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Last URL'
)->addIndex(
    $installer->getIdxName('log_visitor_online', ['visitor_type']),
    ['visitor_type']
)->addIndex(
    $installer->getIdxName('log_visitor_online', ['first_visit_at', 'last_visit_at']),
    ['first_visit_at', 'last_visit_at']
)->addIndex(
    $installer->getIdxName('log_visitor_online', ['customer_id']),
    ['customer_id']
)->setComment(
    'Log Visitor Online Table'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
