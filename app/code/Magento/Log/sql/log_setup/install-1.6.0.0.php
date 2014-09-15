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
/* @var $installer \Magento\Framework\Module\Setup */

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
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Log ID'
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true),
    'Visitor ID'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Customer ID'
)->addColumn(
    'login_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Login Time'
)->addColumn(
    'logout_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Logout Time'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Store ID'
)->addIndex(
    $installer->getIdxName('log_customer', array('visitor_id')),
    array('visitor_id')
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
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Quote ID'
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true),
    'Visitor ID'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Creation Time'
)->addColumn(
    'deleted_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
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
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Summary ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Store ID'
)->addColumn(
    'type_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true),
    'Type ID'
)->addColumn(
    'visitor_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Visitor Count'
)->addColumn(
    'customer_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('nullable' => false, 'default' => '0'),
    'Customer Count'
)->addColumn(
    'add_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
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
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Type ID'
)->addColumn(
    'type_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    array('nullable' => true, 'default' => null),
    'Type Code'
)->addColumn(
    'period',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Period'
)->addColumn(
    'period_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    6,
    array('nullable' => false, 'default' => 'MINUTE'),
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
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'URL ID'
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true),
    'Visitor ID'
)->addColumn(
    'visit_time',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Visit Time'
)->addIndex(
    $installer->getIdxName('log_url', array('visitor_id')),
    array('visitor_id')
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
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'URL ID'
)->addColumn(
    'url',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => true, 'default' => null),
    'URL'
)->addColumn(
    'referer',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
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
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Visitor ID'
)->addColumn(
    'first_visit_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'First Visit Time'
)->addColumn(
    'last_visit_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Last Visit Time'
)->addColumn(
    'last_url_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Last URL ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Store ID'
)->setComment(
    'Log Visitors Table'
);
$installer->getConnection()->createTable($table);
$installer->getConnection()->addForeignKey(
    $installer->getFkName('log_visitor', 'visitor_id', 'customer_visitor', 'visitor_id'),
    $installer->getTable('log_visitor'),
    'visitor_id',
    $installer->getTable('customer_visitor'),
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
);

/**
 * Create table 'log_visitor_info'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('log_visitor_info')
)->addColumn(
    'visitor_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'),
    'Visitor ID'
)->addColumn(
    'http_referer',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'HTTP Referrer'
)->addColumn(
    'http_user_agent',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'HTTP User-Agent'
)->addColumn(
    'http_accept_charset',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'HTTP Accept-Charset'
)->addColumn(
    'http_accept_language',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'HTTP Accept-Language'
)->addColumn(
    'server_addr',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array(),
    'Server Address'
)->addColumn(
    'remote_addr',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array(),
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
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Visitor ID'
)->addColumn(
    'visitor_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    1,
    array('nullable' => false),
    'Visitor Type'
)->addColumn(
    'remote_addr',
    \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
    null,
    array('nullable' => false),
    'Remote Address'
)->addColumn(
    'first_visit_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'First Visit Time'
)->addColumn(
    'last_visit_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Last Visit Time'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true),
    'Customer ID'
)->addColumn(
    'last_url',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array(),
    'Last URL'
)->addIndex(
    $installer->getIdxName('log_visitor_online', array('visitor_type')),
    array('visitor_type')
)->addIndex(
    $installer->getIdxName('log_visitor_online', array('first_visit_at', 'last_visit_at')),
    array('first_visit_at', 'last_visit_at')
)->addIndex(
    $installer->getIdxName('log_visitor_online', array('customer_id')),
    array('customer_id')
)->setComment(
    'Log Visitor Online Table'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
