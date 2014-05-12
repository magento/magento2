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

/**
 * ProductAlert install
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
$installer = $this;
/* @var $installer \Magento\Framework\Module\Setup */

$installer->startSetup();
/**
 * Create table 'product_alert_price'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('product_alert_price')
)->addColumn(
    'alert_price_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Product alert price id'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Customer id'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product id'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false, 'default' => '0.0000'),
    'Price amount'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Website id'
)->addColumn(
    'add_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Product alert add date'
)->addColumn(
    'last_send_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Product alert last send date'
)->addColumn(
    'send_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product alert send count'
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product alert status'
)->addIndex(
    $installer->getIdxName('product_alert_price', array('customer_id')),
    array('customer_id')
)->addIndex(
    $installer->getIdxName('product_alert_price', array('product_id')),
    array('product_id')
)->addIndex(
    $installer->getIdxName('product_alert_price', array('website_id')),
    array('website_id')
)->addForeignKey(
    $installer->getFkName('product_alert_price', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('product_alert_price', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('product_alert_price', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Product Alert Price'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'product_alert_stock'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('product_alert_stock')
)->addColumn(
    'alert_stock_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Product alert stock id'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Customer id'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product id'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Website id'
)->addColumn(
    'add_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array('nullable' => false),
    'Product alert add date'
)->addColumn(
    'send_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Product alert send date'
)->addColumn(
    'send_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Send Count'
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product alert status'
)->addIndex(
    $installer->getIdxName('product_alert_stock', array('customer_id')),
    array('customer_id')
)->addIndex(
    $installer->getIdxName('product_alert_stock', array('product_id')),
    array('product_id')
)->addIndex(
    $installer->getIdxName('product_alert_stock', array('website_id')),
    array('website_id')
)->addForeignKey(
    $installer->getFkName('product_alert_stock', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $installer->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('product_alert_stock', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $installer->getFkName('product_alert_stock', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Product Alert Stock'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
