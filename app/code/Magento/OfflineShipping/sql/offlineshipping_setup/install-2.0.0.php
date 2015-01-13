<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $installer \Magento\Setup\Module\SetupModule */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'shipping_tablerate'
 */
$table = $installer->getConnection()->newTable(
    $installer->getTable('shipping_tablerate')
)->addColumn(
    'pk',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Primary key'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Website Id'
)->addColumn(
    'dest_country_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    4,
    ['nullable' => false, 'default' => '0'],
    'Destination coutry ISO/2 or ISO/3 code'
)->addColumn(
    'dest_region_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Destination Region Id'
)->addColumn(
    'dest_zip',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    10,
    ['nullable' => false, 'default' => '*'],
    'Destination Post Code (Zip)'
)->addColumn(
    'condition_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    20,
    ['nullable' => false],
    'Rate Condition name'
)->addColumn(
    'condition_value',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Rate condition value'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Price'
)->addColumn(
    'cost',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Cost'
)->addIndex(
    $installer->getIdxName(
        'shipping_tablerate',
        ['website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['website_id', 'dest_country_id', 'dest_region_id', 'dest_zip', 'condition_name', 'condition_value'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->setComment(
    'Shipping Tablerate'
);
$installer->getConnection()->createTable($table);

$installer->getConnection()->addColumn(
    $installer->getTable('salesrule'),
    'simple_free_shipping',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Simple Free Shipping'
);
$installer->getConnection()->addColumn(
    $installer->getTable('sales_order_item'),
    'free_shipping',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Free Shipping'
);
$installer->getConnection()->addColumn(
    $installer->getTable('sales_quote_address'),
    'free_shipping',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Free Shipping'
);
$installer->getConnection()->addColumn(
    $installer->getTable('sales_quote_item'),
    'free_shipping',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Free Shipping'
);
$installer->getConnection()->addColumn(
    $installer->getTable('sales_quote_address_item'),
    'free_shipping',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Free Shipping'
);

$installer->endSetup();
