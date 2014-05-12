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
 * Create table 'wishlist'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('wishlist')
)->addColumn(
    'wishlist_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Wishlist ID'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Customer ID'
)->addColumn(
    'shared',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Sharing flag (0 or 1)'
)->addColumn(
    'sharing_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    array(),
    'Sharing encrypted code'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Last updated date'
)->addIndex(
    $installer->getIdxName('wishlist', 'shared'),
    'shared'
)->addIndex(
    $installer->getIdxName('wishlist', 'customer_id', \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE),
    'customer_id',
    array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE)
)->addForeignKey(
    $installer->getFkName('wishlist', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $installer->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Wishlist main Table'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'wishlist_item'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('wishlist_item')
)->addColumn(
    'wishlist_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Wishlist item ID'
)->addColumn(
    'wishlist_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Wishlist ID'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false, 'default' => '0'),
    'Product ID'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    array('unsigned' => true, 'nullable' => true),
    'Store ID'
)->addColumn(
    'added_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    array(),
    'Add date and time'
)->addColumn(
    'description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array(),
    'Short description of wish list item'
)->addColumn(
    'qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    array('nullable' => false),
    'Qty'
)->addIndex(
    $installer->getIdxName('wishlist_item', 'wishlist_id'),
    'wishlist_id'
)->addForeignKey(
    $installer->getFkName('wishlist_item', 'wishlist_id', 'wishlist', 'wishlist_id'),
    'wishlist_id',
    $installer->getTable('wishlist'),
    'wishlist_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addIndex(
    $installer->getIdxName('wishlist_item', 'product_id'),
    'product_id'
)->addForeignKey(
    $installer->getFkName('wishlist_item', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $installer->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addIndex(
    $installer->getIdxName('wishlist_item', 'store_id'),
    'store_id'
)->addForeignKey(
    $installer->getFkName('wishlist_item', 'store_id', 'store', 'store_id'),
    'store_id',
    $installer->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Wishlist items'
);
$installer->getConnection()->createTable($table);

/**
 * Create table 'wishlist_item_option'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('wishlist_item_option')
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
    'Option Id'
)->addColumn(
    'wishlist_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Wishlist Item Id'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    array('unsigned' => true, 'nullable' => false),
    'Product Id'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    array('nullable' => false),
    'Code'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    array('nullable' => true),
    'Value'
)->addForeignKey(
    $installer->getFkName('wishlist_item_option', 'wishlist_item_id', 'wishlist_item', 'wishlist_item_id'),
    'wishlist_item_id',
    $installer->getTable('wishlist_item'),
    'wishlist_item_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Wishlist Item Option Table'
);
$installer->getConnection()->createTable($table);

$installer->endSetup();
