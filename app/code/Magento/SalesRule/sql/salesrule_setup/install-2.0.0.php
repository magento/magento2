<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $this \Magento\Framework\Module\Setup */
$this->startSetup();

/**
 * Create table 'salesrule'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('salesrule')
)->addColumn(
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Rule Id'
)->addColumn(
    'name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Name'
)->addColumn(
    'description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Description'
)->addColumn(
    'from_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    ['nullable' => true, 'default' => null],
    'From Date'
)->addColumn(
    'to_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
    null,
    ['nullable' => true, 'default' => null],
    'To Date'
)->addColumn(
    'uses_per_customer',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Uses Per Customer'
)->addColumn(
    'is_active',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'default' => '0'],
    'Is Active'
)->addColumn(
    'conditions_serialized',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '2M',
    [],
    'Conditions Serialized'
)->addColumn(
    'actions_serialized',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '2M',
    [],
    'Actions Serialized'
)->addColumn(
    'stop_rules_processing',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'default' => '1'],
    'Stop Rules Processing'
)->addColumn(
    'is_advanced',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
    'Is Advanced'
)->addColumn(
    'product_ids',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Product Ids'
)->addColumn(
    'sort_order',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Sort Order'
)->addColumn(
    'simple_action',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Simple Action'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Discount Amount'
)->addColumn(
    'discount_qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    [],
    'Discount Qty'
)->addColumn(
    'discount_step',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Discount Step'
)->addColumn(
    'apply_to_shipping',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Apply To Shipping'
)->addColumn(
    'times_used',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Times Used'
)->addColumn(
    'is_rss',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'default' => '0'],
    'Is Rss'
)->addColumn(
    'coupon_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
    'Coupon Type'
)->addColumn(
    'use_auto_generation',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => false, 'nullable' => false, 'default' => 0],
    'Use Auto Generation'
)->addColumn(
    'uses_per_coupon',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => 0],
    'User Per Coupon'
)->addIndex(
    $this->getIdxName('salesrule', ['is_active', 'sort_order', 'to_date', 'from_date']),
    ['is_active', 'sort_order', 'to_date', 'from_date']
)->setComment(
    'Salesrule'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'salesrule_coupon'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('salesrule_coupon')
)->addColumn(
    'coupon_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Coupon Id'
)->addColumn(
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Rule Id'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Code'
)->addColumn(
    'usage_limit',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Usage Limit'
)->addColumn(
    'usage_per_customer',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Usage Per Customer'
)->addColumn(
    'times_used',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Times Used'
)->addColumn(
    'expiration_date',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Expiration Date'
)->addColumn(
    'is_primary',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Primary'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => true],
    'Coupon Code Creation Date'
)->addColumn(
    'type',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => true, 'default' => 0],
    'Coupon Code Type'
)->addIndex(
    $this->getIdxName(
        'salesrule_coupon',
        ['code'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['code'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName(
        'salesrule_coupon',
        ['rule_id', 'is_primary'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['rule_id', 'is_primary'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('salesrule_coupon', ['rule_id']),
    ['rule_id']
)->addForeignKey(
    $this->getFkName('salesrule_coupon', 'rule_id', 'salesrule', 'rule_id'),
    'rule_id',
    $this->getTable('salesrule'),
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Salesrule Coupon'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'salesrule_coupon_usage'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('salesrule_coupon_usage')
)->addColumn(
    'coupon_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Coupon Id'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Customer Id'
)->addColumn(
    'times_used',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Times Used'
)->addIndex(
    $this->getIdxName('salesrule_coupon_usage', ['customer_id']),
    ['customer_id']
)->addForeignKey(
    $this->getFkName('salesrule_coupon_usage', 'coupon_id', 'salesrule_coupon', 'coupon_id'),
    'coupon_id',
    $this->getTable('salesrule_coupon'),
    'coupon_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('salesrule_coupon_usage', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $this->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Salesrule Coupon Usage'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'salesrule_customer'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('salesrule_customer')
)->addColumn(
    'rule_customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Rule Customer Id'
)->addColumn(
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Rule Id'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Customer Id'
)->addColumn(
    'times_used',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Times Used'
)->addIndex(
    $this->getIdxName('salesrule_customer', ['rule_id', 'customer_id']),
    ['rule_id', 'customer_id']
)->addIndex(
    $this->getIdxName('salesrule_customer', ['customer_id', 'rule_id']),
    ['customer_id', 'rule_id']
)->addForeignKey(
    $this->getFkName('salesrule_customer', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $this->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('salesrule_customer', 'rule_id', 'salesrule', 'rule_id'),
    'rule_id',
    $this->getTable('salesrule'),
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Salesrule Customer'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'salesrule_label'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('salesrule_label')
)->addColumn(
    'label_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Label Id'
)->addColumn(
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Rule Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Store Id'
)->addColumn(
    'label',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Label'
)->addIndex(
    $this->getIdxName(
        'salesrule_label',
        ['rule_id', 'store_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['rule_id', 'store_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('salesrule_label', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('salesrule_label', 'rule_id', 'salesrule', 'rule_id'),
    'rule_id',
    $this->getTable('salesrule'),
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('salesrule_label', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Salesrule Label'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'salesrule_product_attribute'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('salesrule_product_attribute')
)->addColumn(
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Rule Id'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Website Id'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Customer Group Id'
)->addColumn(
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Attribute Id'
)->addIndex(
    $this->getIdxName('salesrule_product_attribute', ['website_id']),
    ['website_id']
)->addIndex(
    $this->getIdxName('salesrule_product_attribute', ['customer_group_id']),
    ['customer_group_id']
)->addIndex(
    $this->getIdxName('salesrule_product_attribute', ['attribute_id']),
    ['attribute_id']
)->addForeignKey(
    $this->getFkName('salesrule_product_attribute', 'attribute_id', 'eav_attribute', 'attribute_id'),
    'attribute_id',
    $this->getTable('eav_attribute'),
    'attribute_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
)->addForeignKey(
    $this->getFkName('salesrule_product_attribute', 'customer_group_id', 'customer_group', 'customer_group_id'),
    'customer_group_id',
    $this->getTable('customer_group'),
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
)->addForeignKey(
    $this->getFkName('salesrule_product_attribute', 'rule_id', 'salesrule', 'rule_id'),
    'rule_id',
    $this->getTable('salesrule'),
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
)->addForeignKey(
    $this->getFkName('salesrule_product_attribute', 'website_id', 'store_website', 'website_id'),
    'website_id',
    $this->getTable('store_website'),
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_NO_ACTION
)->setComment(
    'Salesrule Product Attribute'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'salesrule_coupon_aggregated'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('salesrule_coupon_aggregated')
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
    ['nullable' => false],
    'Period'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'order_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Order Status'
)->addColumn(
    'coupon_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Coupon Code'
)->addColumn(
    'coupon_uses',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Coupon Uses'
)->addColumn(
    'subtotal_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Subtotal Amount'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Discount Amount'
)->addColumn(
    'total_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Total Amount'
)->addColumn(
    'subtotal_amount_actual',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Subtotal Amount Actual'
)->addColumn(
    'discount_amount_actual',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Discount Amount Actual'
)->addColumn(
    'total_amount_actual',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Total Amount Actual'
)->addColumn(
    'rule_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Rule Name'
)->addIndex(
    $this->getIdxName(
        'salesrule_coupon_aggregated',
        ['period', 'store_id', 'order_status', 'coupon_code'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'order_status', 'coupon_code'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('salesrule_coupon_aggregated', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('salesrule_coupon_aggregated', ['rule_name']),
    ['rule_name']
)->addForeignKey(
    $this->getFkName('salesrule_coupon_aggregated', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Coupon Aggregated'
);
$this->getConnection()->createTable($table);

$this->getConnection()->createTable(
    $this->getConnection()->createTableByDdl(
        $this->getTable('salesrule_coupon_aggregated'),
        $this->getTable('salesrule_coupon_aggregated_updated')
    )
);

/**
 * Create table 'salesrule_coupon_aggregated_order'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('salesrule_coupon_aggregated_order')
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
    ['nullable' => false],
    'Period'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'order_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Order Status'
)->addColumn(
    'coupon_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Coupon Code'
)->addColumn(
    'coupon_uses',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Coupon Uses'
)->addColumn(
    'subtotal_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Subtotal Amount'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Discount Amount'
)->addColumn(
    'total_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    [12, 4],
    ['nullable' => false, 'default' => '0.0000'],
    'Total Amount'
)->addColumn(
    'rule_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Rule Name'
)->addIndex(
    $this->getIdxName(
        'salesrule_coupon_aggregated_order',
        ['period', 'store_id', 'order_status', 'coupon_code'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'order_status', 'coupon_code'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('salesrule_coupon_aggregated_order', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('salesrule_coupon_aggregated_order', ['rule_name']),
    ['rule_name']
)->addForeignKey(
    $this->getFkName('salesrule_coupon_aggregated_order', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Coupon Aggregated Order'
);
$this->getConnection()->createTable($table);

$websitesTable = $this->getTable('store_website');
$customerGroupsTable = $this->getTable('customer_group');
$rulesWebsitesTable = $this->getTable('salesrule_website');
$rulesCustomerGroupsTable = $this->getTable('salesrule_customer_group');

/**
 * Create table 'salesrule_website' if not exists. This table will be used instead of
 * column website_ids of main catalog rules table
 */
$table = $this->getConnection()->newTable(
    $rulesWebsitesTable
)->addColumn(
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Rule Id'
)->addColumn(
    'website_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Website Id'
)->addIndex(
    $this->getIdxName('salesrule_website', ['website_id']),
    ['website_id']
)->addForeignKey(
    $this->getFkName('salesrule_website', 'rule_id', 'salesrule', 'rule_id'),
    'rule_id',
    $this->getTable('salesrule'),
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('salesrule_website', 'website_id', 'core/website', 'website_id'),
    'website_id',
    $websitesTable,
    'website_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Rules To Websites Relations'
);

$this->getConnection()->createTable($table);

/**
 * Create table 'salesrule_customer_group' if not exists. This table will be used instead of
 * column customer_group_ids of main catalog rules table
 */
$table = $this->getConnection()->newTable(
    $rulesCustomerGroupsTable
)->addColumn(
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Rule Id'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Customer Group Id'
)->addIndex(
    $this->getIdxName('salesrule_customer_group', ['customer_group_id']),
    ['customer_group_id']
)->addForeignKey(
    $this->getFkName('salesrule_customer_group', 'rule_id', 'salesrule', 'rule_id'),
    'rule_id',
    $this->getTable('salesrule'),
    'rule_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('salesrule_customer_group', 'customer_group_id', 'customer_group', 'customer_group_id'),
    'customer_group_id',
    $customerGroupsTable,
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Rules To Customer Groups Relations'
);

$this->getConnection()->createTable($table);

$this->endSetup();
