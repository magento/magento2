<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Setup\Module\SetupModule */
$this->startSetup();

/**
 * Create table 'tax_class'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('tax_class')
)->addColumn(
    'class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'Class Id'
)->addColumn(
    'class_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'Class Name'
)->addColumn(
    'class_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    8,
    ['nullable' => false, 'default' => 'CUSTOMER'],
    'Class Type'
)->setComment(
    'Tax Class'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'tax_calculation_rule'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('tax_calculation_rule')
)->addColumn(
    'tax_calculation_rule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'Tax Calculation Rule Id'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'Code'
)->addColumn(
    'priority',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false],
    'Priority'
)->addColumn(
    'position',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false],
    'Position'
)->addColumn(
    'calculate_subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false],
    'Calculate off subtotal option'
)->addIndex(
    $this->getIdxName('tax_calculation_rule', ['priority', 'position']),
    ['priority', 'position']
)->addIndex(
    $this->getIdxName('tax_calculation_rule', ['code']),
    ['code']
)->setComment(
    'Tax Calculation Rule'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'tax_calculation_rate'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('tax_calculation_rate')
)->addColumn(
    'tax_calculation_rate_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'Tax Calculation Rate Id'
)->addColumn(
    'tax_country_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    2,
    ['nullable' => false],
    'Tax Country Id'
)->addColumn(
    'tax_region_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false],
    'Tax Region Id'
)->addColumn(
    'tax_postcode',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    21,
    [],
    'Tax Postcode'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'Code'
)->addColumn(
    'rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false],
    'Rate'
)->addColumn(
    'zip_is_range',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    [],
    'Zip Is Range'
)->addColumn(
    'zip_from',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Zip From'
)->addColumn(
    'zip_to',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Zip To'
)->addIndex(
    $this->getIdxName('tax_calculation_rate', ['tax_country_id', 'tax_region_id', 'tax_postcode']),
    ['tax_country_id', 'tax_region_id', 'tax_postcode']
)->addIndex(
    $this->getIdxName('tax_calculation_rate', ['code']),
    ['code']
)->addIndex(
    $this->getIdxName(
        'tax_calculation_rate',
        ['tax_calculation_rate_id', 'tax_country_id', 'tax_region_id', 'zip_is_range', 'tax_postcode']
    ),
    ['tax_calculation_rate_id', 'tax_country_id', 'tax_region_id', 'zip_is_range', 'tax_postcode']
)->setComment(
    'Tax Calculation Rate'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'tax_calculation'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('tax_calculation')
)->addColumn(
    'tax_calculation_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'Tax Calculation Id'
)->addColumn(
    'tax_calculation_rate_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false],
    'Tax Calculation Rate Id'
)->addColumn(
    'tax_calculation_rule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false],
    'Tax Calculation Rule Id'
)->addColumn(
    'customer_tax_class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false],
    'Customer Tax Class Id'
)->addColumn(
    'product_tax_class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false],
    'Product Tax Class Id'
)->addIndex(
    $this->getIdxName('tax_calculation', ['tax_calculation_rule_id']),
    ['tax_calculation_rule_id']
)->addIndex(
    $this->getIdxName('tax_calculation', ['customer_tax_class_id']),
    ['customer_tax_class_id']
)->addIndex(
    $this->getIdxName('tax_calculation', ['product_tax_class_id']),
    ['product_tax_class_id']
)->addIndex(
    $this->getIdxName(
        'tax_calculation',
        ['tax_calculation_rate_id', 'customer_tax_class_id', 'product_tax_class_id']
    ),
    ['tax_calculation_rate_id', 'customer_tax_class_id', 'product_tax_class_id']
)->addForeignKey(
    $this->getFkName('tax_calculation', 'product_tax_class_id', 'tax_class', 'class_id'),
    'product_tax_class_id',
    $this->getTable('tax_class'),
    'class_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('tax_calculation', 'customer_tax_class_id', 'tax_class', 'class_id'),
    'customer_tax_class_id',
    $this->getTable('tax_class'),
    'class_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName(
        'tax_calculation',
        'tax_calculation_rate_id',
        'tax_calculation_rate',
        'tax_calculation_rate_id'
    ),
    'tax_calculation_rate_id',
    $this->getTable('tax_calculation_rate'),
    'tax_calculation_rate_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName(
        'tax_calculation',
        'tax_calculation_rule_id',
        'tax_calculation_rule',
        'tax_calculation_rule_id'
    ),
    'tax_calculation_rule_id',
    $this->getTable('tax_calculation_rule'),
    'tax_calculation_rule_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Tax Calculation'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'tax_calculation_rate_title'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('tax_calculation_rate_title')
)->addColumn(
    'tax_calculation_rate_title_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'Tax Calculation Rate Title Id'
)->addColumn(
    'tax_calculation_rate_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false],
    'Tax Calculation Rate Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Store Id'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'Value'
)->addIndex(
    $this->getIdxName('tax_calculation_rate_title', ['tax_calculation_rate_id', 'store_id']),
    ['tax_calculation_rate_id', 'store_id']
)->addIndex(
    $this->getIdxName('tax_calculation_rate_title', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('tax_calculation_rate_title', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName(
        'tax_calculation_rate_title',
        'tax_calculation_rate_id',
        'tax_calculation_rate',
        'tax_calculation_rate_id'
    ),
    'tax_calculation_rate_id',
    $this->getTable('tax_calculation_rate'),
    'tax_calculation_rate_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Tax Calculation Rate Title'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'tax_order_aggregated_created'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('tax_order_aggregated_created')
)->addColumn(
    'id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Id'
)->addColumn(
    'period',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    ['nullable' => true],
    'Period'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'Code'
)->addColumn(
    'order_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    ['nullable' => false],
    'Order Status'
)->addColumn(
    'percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
    null,
    [],
    'Percent'
)->addColumn(
    'orders_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Orders Count'
)->addColumn(
    'tax_base_amount_sum',
    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
    null,
    [],
    'Tax Base Amount Sum'
)->addIndex(
    $this->getIdxName(
        'tax_order_aggregated_created',
        ['period', 'store_id', 'code', 'percent', 'order_status'],
        true
    ),
    ['period', 'store_id', 'code', 'percent', 'order_status'],
    ['type' => 'unique']
)->addIndex(
    $this->getIdxName('tax_order_aggregated_created', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('tax_order_aggregated_created', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Tax Order Aggregation'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'tax_order_aggregated_updated'
 */
$this->getConnection()->createTable(
    $this->getConnection()->createTableByDdl(
        $this->getTable('tax_order_aggregated_created'),
        $this->getTable('tax_order_aggregated_updated')
    )
);

/**
 * Create table 'sales_order_tax_item'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_tax_item')
)->addColumn(
    'tax_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Tax Item Id'
)->addColumn(
    'tax_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Tax Id'
)->addColumn(
    'item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => true],
    'Item Id'
)->addColumn(
    'tax_percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false],
    'Real Tax Percent For Item'
)->addColumn(
    'amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false],
    'Tax amount for the item and tax rate'
)->addColumn(
    'base_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false],
    'Base tax amount for the item and tax rate'
)->addColumn(
    'real_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false],
    'Real tax amount for the item and tax rate'
)->addColumn(
    'real_base_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false],
    'Real base tax amount for the item and tax rate'
)->addColumn(
    'associated_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => true, 'unsigned' => true],
    'Id of the associated item'
)->addColumn(
    'taxable_item_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => false],
    'Type of the taxable item'
)->addIndex(
    $this->getIdxName('sales_order_tax_item', ['item_id']),
    ['item_id']
)->addIndex(
    $this->getIdxName(
        'sales_order_tax_item',
        ['tax_id', 'item_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['tax_id', 'item_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addForeignKey(
    $this->getFkName('sales_order_tax_item', 'associated_item_id', 'sales_order_item', 'item_id'),
    'associated_item_id',
    $this->getTable('sales_order_item'),
    'item_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_order_tax_item', 'tax_id', 'sales_order_tax', 'tax_id'),
    'tax_id',
    $this->getTable('sales_order_tax'),
    'tax_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_order_tax_item', 'item_id', 'sales_order_item', 'item_id'),
    'item_id',
    $this->getTable('sales_order_item'),
    'item_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Order Tax Item'
);
$this->getConnection()->createTable($table);

$this->endSetup();
