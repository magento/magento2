<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $this \Magento\Sales\Model\Resource\Setup */
$this->startSetup();

/**
 * Create table 'sales_order'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'state',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'State'
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Status'
)->addColumn(
    'coupon_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Coupon Code'
)->addColumn(
    'protect_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Protect Code'
)->addColumn(
    'shipping_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Shipping Description'
)->addColumn(
    'is_virtual',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Virtual'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Customer Id'
)->addColumn(
    'base_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Discount Amount'
)->addColumn(
    'base_discount_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Discount Canceled'
)->addColumn(
    'base_discount_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Discount Invoiced'
)->addColumn(
    'base_discount_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Discount Refunded'
)->addColumn(
    'base_grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Grand Total'
)->addColumn(
    'base_shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Amount'
)->addColumn(
    'base_shipping_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Canceled'
)->addColumn(
    'base_shipping_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Invoiced'
)->addColumn(
    'base_shipping_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Refunded'
)->addColumn(
    'base_shipping_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Tax Amount'
)->addColumn(
    'base_shipping_tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Tax Refunded'
)->addColumn(
    'base_subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal'
)->addColumn(
    'base_subtotal_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal Canceled'
)->addColumn(
    'base_subtotal_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal Invoiced'
)->addColumn(
    'base_subtotal_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal Refunded'
)->addColumn(
    'base_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Amount'
)->addColumn(
    'base_tax_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Canceled'
)->addColumn(
    'base_tax_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Invoiced'
)->addColumn(
    'base_tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Refunded'
)->addColumn(
    'base_to_global_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Global Rate'
)->addColumn(
    'base_to_order_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Order Rate'
)->addColumn(
    'base_total_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Canceled'
)->addColumn(
    'base_total_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Invoiced'
)->addColumn(
    'base_total_invoiced_cost',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Invoiced Cost'
)->addColumn(
    'base_total_offline_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Offline Refunded'
)->addColumn(
    'base_total_online_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Online Refunded'
)->addColumn(
    'base_total_paid',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Paid'
)->addColumn(
    'base_total_qty_ordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Qty Ordered'
)->addColumn(
    'base_total_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Refunded'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Amount'
)->addColumn(
    'discount_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Canceled'
)->addColumn(
    'discount_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Invoiced'
)->addColumn(
    'discount_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Refunded'
)->addColumn(
    'grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Grand Total'
)->addColumn(
    'shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Amount'
)->addColumn(
    'shipping_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Canceled'
)->addColumn(
    'shipping_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Invoiced'
)->addColumn(
    'shipping_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Refunded'
)->addColumn(
    'shipping_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Tax Amount'
)->addColumn(
    'shipping_tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Tax Refunded'
)->addColumn(
    'store_to_base_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Store To Base Rate'
)->addColumn(
    'store_to_order_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Store To Order Rate'
)->addColumn(
    'subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal'
)->addColumn(
    'subtotal_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal Canceled'
)->addColumn(
    'subtotal_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal Invoiced'
)->addColumn(
    'subtotal_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal Refunded'
)->addColumn(
    'tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Amount'
)->addColumn(
    'tax_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Canceled'
)->addColumn(
    'tax_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Invoiced'
)->addColumn(
    'tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Refunded'
)->addColumn(
    'total_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Canceled'
)->addColumn(
    'total_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Invoiced'
)->addColumn(
    'total_offline_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Offline Refunded'
)->addColumn(
    'total_online_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Online Refunded'
)->addColumn(
    'total_paid',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Paid'
)->addColumn(
    'total_qty_ordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Qty Ordered'
)->addColumn(
    'total_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Refunded'
)->addColumn(
    'can_ship_partially',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Can Ship Partially'
)->addColumn(
    'can_ship_partially_item',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Can Ship Partially Item'
)->addColumn(
    'customer_is_guest',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Customer Is Guest'
)->addColumn(
    'customer_note_notify',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Customer Note Notify'
)->addColumn(
    'billing_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Billing Address Id'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    [],
    'Customer Group Id'
)->addColumn(
    'edit_increment',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Edit Increment'
)->addColumn(
    'email_sent',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Email Sent'
)->addColumn(
    'forced_shipment_with_invoice',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Forced Do Shipment With Invoice'
)->addColumn(
    'payment_auth_expiration',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Payment Authorization Expiration'
)->addColumn(
    'quote_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Quote Address Id'
)->addColumn(
    'quote_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Quote Id'
)->addColumn(
    'shipping_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Shipping Address Id'
)->addColumn(
    'adjustment_negative',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Adjustment Negative'
)->addColumn(
    'adjustment_positive',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Adjustment Positive'
)->addColumn(
    'base_adjustment_negative',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Adjustment Negative'
)->addColumn(
    'base_adjustment_positive',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Adjustment Positive'
)->addColumn(
    'base_shipping_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Discount Amount'
)->addColumn(
    'base_subtotal_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal Incl Tax'
)->addColumn(
    'base_total_due',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Due'
)->addColumn(
    'payment_authorization_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Payment Authorization Amount'
)->addColumn(
    'shipping_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Discount Amount'
)->addColumn(
    'subtotal_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal Incl Tax'
)->addColumn(
    'total_due',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Due'
)->addColumn(
    'weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Weight'
)->addColumn(
    'customer_dob',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
    null,
    [],
    'Customer Dob'
)->addColumn(
    'increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Increment Id'
)->addColumn(
    'applied_rule_ids',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Applied Rule Ids'
)->addColumn(
    'base_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Base Currency Code'
)->addColumn(
    'customer_email',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Email'
)->addColumn(
    'customer_firstname',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Firstname'
)->addColumn(
    'customer_lastname',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Lastname'
)->addColumn(
    'customer_middlename',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Middlename'
)->addColumn(
    'customer_prefix',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Prefix'
)->addColumn(
    'customer_suffix',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Suffix'
)->addColumn(
    'customer_taxvat',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Taxvat'
)->addColumn(
    'discount_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Discount Description'
)->addColumn(
    'ext_customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Ext Customer Id'
)->addColumn(
    'ext_order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Ext Order Id'
)->addColumn(
    'global_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Global Currency Code'
)->addColumn(
    'hold_before_state',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Hold Before State'
)->addColumn(
    'hold_before_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Hold Before Status'
)->addColumn(
    'order_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Order Currency Code'
)->addColumn(
    'original_increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Original Increment Id'
)->addColumn(
    'relation_child_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Relation Child Id'
)->addColumn(
    'relation_child_real_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Relation Child Real Id'
)->addColumn(
    'relation_parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Relation Parent Id'
)->addColumn(
    'relation_parent_real_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Relation Parent Real Id'
)->addColumn(
    'remote_ip',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Remote Ip'
)->addColumn(
    'shipping_method',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Shipping Method'
)->addColumn(
    'store_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Store Currency Code'
)->addColumn(
    'store_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Store Name'
)->addColumn(
    'x_forwarded_for',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'X Forwarded For'
)->addColumn(
    'customer_note',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Customer Note'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
    'Updated At'
)->addColumn(
    'total_item_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Total Item Count'
)->addColumn(
    'customer_gender',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Customer Gender'
)->addColumn(
    'hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Amount'
)->addColumn(
    'base_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Amount'
)->addColumn(
    'shipping_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Hidden Tax Amount'
)->addColumn(
    'base_shipping_hidden_tax_amnt',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Hidden Tax Amount'
)->addColumn(
    'hidden_tax_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Invoiced'
)->addColumn(
    'base_hidden_tax_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Invoiced'
)->addColumn(
    'hidden_tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Refunded'
)->addColumn(
    'base_hidden_tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Refunded'
)->addColumn(
    'shipping_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Incl Tax'
)->addColumn(
    'base_shipping_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Incl Tax'
)->addColumn(
    'coupon_rule_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true],
    'Coupon Sales Rule Name'
)->addIndex(
    $this->getIdxName('sales_order', ['status']),
    ['status']
)->addIndex(
    $this->getIdxName('sales_order', ['state']),
    ['state']
)->addIndex(
    $this->getIdxName('sales_order', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName(
        'sales_order',
        ['increment_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['increment_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_order', ['created_at']),
    ['created_at']
)->addIndex(
    $this->getIdxName('sales_order', ['customer_id']),
    ['customer_id']
)->addIndex(
    $this->getIdxName('sales_order', ['ext_order_id']),
    ['ext_order_id']
)->addIndex(
    $this->getIdxName('sales_order', ['quote_id']),
    ['quote_id']
)->addIndex(
    $this->getIdxName('sales_order', ['updated_at']),
    ['updated_at']
)->addForeignKey(
    $this->getFkName('sales_order', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $this->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_order', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Order'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_grid'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_grid')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Status'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'store_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Store Name'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Customer Id'
)->addColumn(
    'base_grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Grand Total'
)->addColumn(
    'base_total_paid',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Paid'
)->addColumn(
    'grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Grand Total'
)->addColumn(
    'total_paid',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Paid'
)->addColumn(
    'increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Increment Id'
)->addColumn(
    'base_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Base Currency Code'
)->addColumn(
    'order_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Order Currency Code'
)->addColumn(
    'shipping_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Shipping Name'
)->addColumn(
    'billing_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Billing Name'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Updated At'
)->addIndex(
    $this->getIdxName('sales_order_grid', ['status']),
    ['status']
)->addIndex(
    $this->getIdxName('sales_order_grid', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_order_grid', ['base_grand_total']),
    ['base_grand_total']
)->addIndex(
    $this->getIdxName('sales_order_grid', ['base_total_paid']),
    ['base_total_paid']
)->addIndex(
    $this->getIdxName('sales_order_grid', ['grand_total']),
    ['grand_total']
)->addIndex(
    $this->getIdxName('sales_order_grid', ['total_paid']),
    ['total_paid']
)->addIndex(
    $this->getIdxName(
        'sales_order_grid',
        ['increment_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['increment_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_order_grid', ['shipping_name']),
    ['shipping_name']
)->addIndex(
    $this->getIdxName('sales_order_grid', ['billing_name']),
    ['billing_name']
)->addIndex(
    $this->getIdxName('sales_order_grid', ['created_at']),
    ['created_at']
)->addIndex(
    $this->getIdxName('sales_order_grid', ['customer_id']),
    ['customer_id']
)->addIndex(
    $this->getIdxName('sales_order_grid', ['updated_at']),
    ['updated_at']
)->addForeignKey(
    $this->getFkName('sales_order_grid', 'customer_id', 'customer_entity', 'entity_id'),
    'customer_id',
    $this->getTable('customer_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_order_grid', 'entity_id', 'sales_order', 'entity_id'),
    'entity_id',
    $this->getTable('sales_order'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_order_grid', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Order Grid'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_address'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_address')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Parent Id'
)->addColumn(
    'customer_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Customer Address Id'
)->addColumn(
    'quote_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Quote Address Id'
)->addColumn(
    'region_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Region Id'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Customer Id'
)->addColumn(
    'fax',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Fax'
)->addColumn(
    'region',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Region'
)->addColumn(
    'postcode',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Postcode'
)->addColumn(
    'lastname',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Lastname'
)->addColumn(
    'street',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Street'
)->addColumn(
    'city',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'City'
)->addColumn(
    'email',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Email'
)->addColumn(
    'telephone',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Phone Number'
)->addColumn(
    'country_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    2,
    [],
    'Country Id'
)->addColumn(
    'firstname',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Firstname'
)->addColumn(
    'address_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Address Type'
)->addColumn(
    'prefix',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Prefix'
)->addColumn(
    'middlename',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Middlename'
)->addColumn(
    'suffix',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Suffix'
)->addColumn(
    'company',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Company'
)->addIndex(
    $this->getIdxName('sales_order_address', ['parent_id']),
    ['parent_id']
)->addForeignKey(
    $this->getFkName('sales_order_address', 'parent_id', 'sales_order', 'entity_id'),
    'parent_id',
    $this->getTable('sales_order'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Order Address'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_status_history'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_status_history')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Parent Id'
)->addColumn(
    'is_customer_notified',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Is Customer Notified'
)->addColumn(
    'is_visible_on_front',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Is Visible On Front'
)->addColumn(
    'comment',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Comment'
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Status'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addColumn(
    'entity_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => true],
    'Shows what entity history is bind to.'
)->addIndex(
    $this->getIdxName('sales_order_status_history', ['parent_id']),
    ['parent_id']
)->addIndex(
    $this->getIdxName('sales_order_status_history', ['created_at']),
    ['created_at']
)->addForeignKey(
    $this->getFkName('sales_order_status_history', 'parent_id', 'sales_order', 'entity_id'),
    'parent_id',
    $this->getTable('sales_order'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Order Status History'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_item'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_item')
)->addColumn(
    'item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Item Id'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Order Id'
)->addColumn(
    'parent_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Parent Item Id'
)->addColumn(
    'quote_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Quote Item Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false],
    'Updated At'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Product Id'
)->addColumn(
    'product_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Product Type'
)->addColumn(
    'product_options',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Product Options'
)->addColumn(
    'weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Weight'
)->addColumn(
    'is_virtual',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Virtual'
)->addColumn(
    'sku',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Sku'
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
    'applied_rule_ids',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Applied Rule Ids'
)->addColumn(
    'additional_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Data'
)->addColumn(
    'is_qty_decimal',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Qty Decimal'
)->addColumn(
    'no_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'No Discount'
)->addColumn(
    'qty_backordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Qty Backordered'
)->addColumn(
    'qty_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Qty Canceled'
)->addColumn(
    'qty_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Qty Invoiced'
)->addColumn(
    'qty_ordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Qty Ordered'
)->addColumn(
    'qty_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Qty Refunded'
)->addColumn(
    'qty_shipped',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Qty Shipped'
)->addColumn(
    'base_cost',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Cost'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Price'
)->addColumn(
    'base_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Price'
)->addColumn(
    'original_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Original Price'
)->addColumn(
    'base_original_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Original Price'
)->addColumn(
    'tax_percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Tax Percent'
)->addColumn(
    'tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Tax Amount'
)->addColumn(
    'base_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Tax Amount'
)->addColumn(
    'tax_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Tax Invoiced'
)->addColumn(
    'base_tax_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Tax Invoiced'
)->addColumn(
    'discount_percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Discount Percent'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Discount Amount'
)->addColumn(
    'base_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Discount Amount'
)->addColumn(
    'discount_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Discount Invoiced'
)->addColumn(
    'base_discount_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Discount Invoiced'
)->addColumn(
    'amount_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Amount Refunded'
)->addColumn(
    'base_amount_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Amount Refunded'
)->addColumn(
    'row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Row Total'
)->addColumn(
    'base_row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Row Total'
)->addColumn(
    'row_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Row Invoiced'
)->addColumn(
    'base_row_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Row Invoiced'
)->addColumn(
    'row_weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Row Weight'
)->addColumn(
    'base_tax_before_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Before Discount'
)->addColumn(
    'tax_before_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Before Discount'
)->addColumn(
    'ext_order_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Ext Order Item Id'
)->addColumn(
    'locked_do_invoice',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Locked Do Invoice'
)->addColumn(
    'locked_do_ship',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Locked Do Ship'
)->addColumn(
    'price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Price Incl Tax'
)->addColumn(
    'base_price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Price Incl Tax'
)->addColumn(
    'row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Row Total Incl Tax'
)->addColumn(
    'base_row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Row Total Incl Tax'
)->addColumn(
    'hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Amount'
)->addColumn(
    'base_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Amount'
)->addColumn(
    'hidden_tax_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Invoiced'
)->addColumn(
    'base_hidden_tax_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Invoiced'
)->addColumn(
    'hidden_tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Refunded'
)->addColumn(
    'base_hidden_tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Refunded'
)->addColumn(
    'tax_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Canceled'
)->addColumn(
    'hidden_tax_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Canceled'
)->addColumn(
    'tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Refunded'
)->addColumn(
    'base_tax_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Refunded'
)->addColumn(
    'discount_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Refunded'
)->addColumn(
    'base_discount_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Discount Refunded'
)->addIndex(
    $this->getIdxName('sales_order_item', ['order_id']),
    ['order_id']
)->addIndex(
    $this->getIdxName('sales_order_item', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_order_item', 'order_id', 'sales_order', 'entity_id'),
    'order_id',
    $this->getTable('sales_order'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_order_item', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Order Item'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_payment'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_payment')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Parent Id'
)->addColumn(
    'base_shipping_captured',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Captured'
)->addColumn(
    'shipping_captured',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Captured'
)->addColumn(
    'amount_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Amount Refunded'
)->addColumn(
    'base_amount_paid',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Amount Paid'
)->addColumn(
    'amount_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Amount Canceled'
)->addColumn(
    'base_amount_authorized',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Amount Authorized'
)->addColumn(
    'base_amount_paid_online',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Amount Paid Online'
)->addColumn(
    'base_amount_refunded_online',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Amount Refunded Online'
)->addColumn(
    'base_shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Amount'
)->addColumn(
    'shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Amount'
)->addColumn(
    'amount_paid',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Amount Paid'
)->addColumn(
    'amount_authorized',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Amount Authorized'
)->addColumn(
    'base_amount_ordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Amount Ordered'
)->addColumn(
    'base_shipping_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Refunded'
)->addColumn(
    'shipping_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Refunded'
)->addColumn(
    'base_amount_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Amount Refunded'
)->addColumn(
    'amount_ordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Amount Ordered'
)->addColumn(
    'base_amount_canceled',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Amount Canceled'
)->addColumn(
    'quote_payment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Quote Payment Id'
)->addColumn(
    'additional_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Data'
)->addColumn(
    'cc_exp_month',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Exp Month'
)->addColumn(
    'cc_ss_start_year',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Ss Start Year'
)->addColumn(
    'echeck_bank_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Echeck Bank Name'
)->addColumn(
    'method',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Method'
)->addColumn(
    'cc_debug_request_body',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Debug Request Body'
)->addColumn(
    'cc_secure_verify',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Secure Verify'
)->addColumn(
    'protection_eligibility',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Protection Eligibility'
)->addColumn(
    'cc_approval',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Approval'
)->addColumn(
    'cc_last_4',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Last 4'
)->addColumn(
    'cc_status_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Status Description'
)->addColumn(
    'echeck_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Echeck Type'
)->addColumn(
    'cc_debug_response_serialized',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Debug Response Serialized'
)->addColumn(
    'cc_ss_start_month',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Ss Start Month'
)->addColumn(
    'echeck_account_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Echeck Account Type'
)->addColumn(
    'last_trans_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Last Trans Id'
)->addColumn(
    'cc_cid_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Cid Status'
)->addColumn(
    'cc_owner',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Owner'
)->addColumn(
    'cc_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Type'
)->addColumn(
    'po_number',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Po Number'
)->addColumn(
    'cc_exp_year',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true, 'default' => null],
    'Cc Exp Year'
)->addColumn(
    'cc_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Status'
)->addColumn(
    'echeck_routing_number',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Echeck Routing Number'
)->addColumn(
    'account_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Account Status'
)->addColumn(
    'anet_trans_method',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Anet Trans Method'
)->addColumn(
    'cc_debug_response_body',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Debug Response Body'
)->addColumn(
    'cc_ss_issue',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Ss Issue'
)->addColumn(
    'echeck_account_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Echeck Account Name'
)->addColumn(
    'cc_avs_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Avs Status'
)->addColumn(
    'cc_number_enc',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Number Enc'
)->addColumn(
    'cc_trans_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Trans Id'
)->addColumn(
    'address_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Address Status'
)->addColumn(
    'additional_information',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Information'
)->addIndex(
    $this->getIdxName('sales_order_payment', ['parent_id']),
    ['parent_id']
)->addForeignKey(
    $this->getFkName('sales_order_payment', 'parent_id', 'sales_order', 'entity_id'),
    'parent_id',
    $this->getTable('sales_order'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Order Payment'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_shipment'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_shipment')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'total_weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Weight'
)->addColumn(
    'total_qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Qty'
)->addColumn(
    'email_sent',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Email Sent'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Order Id'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Customer Id'
)->addColumn(
    'shipping_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Shipping Address Id'
)->addColumn(
    'billing_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Billing Address Id'
)->addColumn(
    'shipment_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Shipment Status'
)->addColumn(
    'increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Increment Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Updated At'
)->addColumn(
    'packages',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '20000',
    [],
    'Packed Products in Packages'
)->addColumn(
    'shipping_label',
    \Magento\Framework\DB\Ddl\Table::TYPE_VARBINARY,
    '2m',
    [],
    'Shipping Label Content'
)->addIndex(
    $this->getIdxName('sales_shipment', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_shipment', ['total_qty']),
    ['total_qty']
)->addIndex(
    $this->getIdxName(
        'sales_shipment',
        ['increment_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['increment_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_shipment', ['order_id']),
    ['order_id']
)->addIndex(
    $this->getIdxName('sales_shipment', ['created_at']),
    ['created_at']
)->addIndex(
    $this->getIdxName('sales_shipment', ['updated_at']),
    ['updated_at']
)->addForeignKey(
    $this->getFkName('sales_shipment', 'order_id', 'sales_order', 'entity_id'),
    'order_id',
    $this->getTable('sales_order'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_shipment', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Shipment'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_shipment_grid'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_shipment_grid')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'total_qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Qty'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Order Id'
)->addColumn(
    'shipment_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Shipment Status'
)->addColumn(
    'increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Increment Id'
)->addColumn(
    'order_increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Order Increment Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addColumn(
    'order_created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Order Created At'
)->addColumn(
    'shipping_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Shipping Name'
)->addIndex(
    $this->getIdxName('sales_shipment_grid', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_shipment_grid', ['total_qty']),
    ['total_qty']
)->addIndex(
    $this->getIdxName('sales_shipment_grid', ['order_id']),
    ['order_id']
)->addIndex(
    $this->getIdxName('sales_shipment_grid', ['shipment_status']),
    ['shipment_status']
)->addIndex(
    $this->getIdxName(
        'sales_shipment_grid',
        ['increment_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['increment_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_shipment_grid', ['order_increment_id']),
    ['order_increment_id']
)->addIndex(
    $this->getIdxName('sales_shipment_grid', ['created_at']),
    ['created_at']
)->addIndex(
    $this->getIdxName('sales_shipment_grid', ['order_created_at']),
    ['order_created_at']
)->addIndex(
    $this->getIdxName('sales_shipment_grid', ['shipping_name']),
    ['shipping_name']
)->addForeignKey(
    $this->getFkName('sales_shipment_grid', 'entity_id', 'sales_shipment', 'entity_id'),
    'entity_id',
    $this->getTable('sales_shipment'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_shipment_grid', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Shipment Grid'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_shipment_item'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_shipment_item')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Parent Id'
)->addColumn(
    'row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Row Total'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Price'
)->addColumn(
    'weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Weight'
)->addColumn(
    'qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Qty'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Product Id'
)->addColumn(
    'order_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Order Item Id'
)->addColumn(
    'additional_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Data'
)->addColumn(
    'description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Description'
)->addColumn(
    'name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Name'
)->addColumn(
    'sku',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Sku'
)->addIndex(
    $this->getIdxName('sales_shipment_item', ['parent_id']),
    ['parent_id']
)->addForeignKey(
    $this->getFkName('sales_shipment_item', 'parent_id', 'sales_shipment', 'entity_id'),
    'parent_id',
    $this->getTable('sales_shipment'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Shipment Item'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_shipment_track'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_shipment_track')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Parent Id'
)->addColumn(
    'weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Weight'
)->addColumn(
    'qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Qty'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Order Id'
)->addColumn(
    'track_number',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Number'
)->addColumn(
    'description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Description'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Title'
)->addColumn(
    'carrier_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Carrier Code'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Updated At'
)->addIndex(
    $this->getIdxName('sales_shipment_track', ['parent_id']),
    ['parent_id']
)->addIndex(
    $this->getIdxName('sales_shipment_track', ['order_id']),
    ['order_id']
)->addIndex(
    $this->getIdxName('sales_shipment_track', ['created_at']),
    ['created_at']
)->addForeignKey(
    $this->getFkName('sales_shipment_track', 'parent_id', 'sales_shipment', 'entity_id'),
    'parent_id',
    $this->getTable('sales_shipment'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Shipment Track'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_shipment_comment'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_shipment_comment')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Parent Id'
)->addColumn(
    'is_customer_notified',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Is Customer Notified'
)->addColumn(
    'is_visible_on_front',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Is Visible On Front'
)->addColumn(
    'comment',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Comment'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addIndex(
    $this->getIdxName('sales_shipment_comment', ['created_at']),
    ['created_at']
)->addIndex(
    $this->getIdxName('sales_shipment_comment', ['parent_id']),
    ['parent_id']
)->addForeignKey(
    $this->getFkName('sales_shipment_comment', 'parent_id', 'sales_shipment', 'entity_id'),
    'parent_id',
    $this->getTable('sales_shipment'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Shipment Comment'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_invoice'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_invoice')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'identity' => true],
    'Entity Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'base_grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Grand Total'
)->addColumn(
    'shipping_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Tax Amount'
)->addColumn(
    'tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Amount'
)->addColumn(
    'base_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Amount'
)->addColumn(
    'store_to_order_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Store To Order Rate'
)->addColumn(
    'base_shipping_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Tax Amount'
)->addColumn(
    'base_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Discount Amount'
)->addColumn(
    'base_to_order_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Order Rate'
)->addColumn(
    'grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Grand Total'
)->addColumn(
    'shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Amount'
)->addColumn(
    'subtotal_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal Incl Tax'
)->addColumn(
    'base_subtotal_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal Incl Tax'
)->addColumn(
    'store_to_base_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Store To Base Rate'
)->addColumn(
    'base_shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Amount'
)->addColumn(
    'total_qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Qty'
)->addColumn(
    'base_to_global_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Global Rate'
)->addColumn(
    'subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal'
)->addColumn(
    'base_subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Amount'
)->addColumn(
    'billing_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Billing Address Id'
)->addColumn(
    'is_used_for_refund',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Used For Refund'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Order Id'
)->addColumn(
    'email_sent',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Email Sent'
)->addColumn(
    'can_void_flag',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Can Void Flag'
)->addColumn(
    'state',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'State'
)->addColumn(
    'shipping_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Shipping Address Id'
)->addColumn(
    'store_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Store Currency Code'
)->addColumn(
    'transaction_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Transaction Id'
)->addColumn(
    'order_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Order Currency Code'
)->addColumn(
    'base_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Base Currency Code'
)->addColumn(
    'global_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Global Currency Code'
)->addColumn(
    'increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Increment Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Updated At'
)->addColumn(
    'hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Amount'
)->addColumn(
    'base_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Amount'
)->addColumn(
    'shipping_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Hidden Tax Amount'
)->addColumn(
    'base_shipping_hidden_tax_amnt',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Hidden Tax Amount'
)->addColumn(
    'shipping_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Incl Tax'
)->addColumn(
    'base_shipping_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Incl Tax'
)->addColumn(
    'base_total_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Total Refunded'
)->addColumn(
    'discount_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Discount Description'
)->addIndex(
    $this->getIdxName('sales_invoice', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_invoice', ['grand_total']),
    ['grand_total']
)->addIndex(
    $this->getIdxName('sales_invoice', ['order_id']),
    ['order_id']
)->addIndex(
    $this->getIdxName('sales_invoice', ['state']),
    ['state']
)->addIndex(
    $this->getIdxName(
        'sales_invoice',
        ['increment_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['increment_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_invoice', ['created_at']),
    ['created_at']
)->addForeignKey(
    $this->getFkName('sales_invoice', 'order_id', 'sales_order', 'entity_id'),
    'order_id',
    $this->getTable('sales_order'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_invoice', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Invoice'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_invoice_grid'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_invoice_grid')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'base_grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Grand Total'
)->addColumn(
    'grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Grand Total'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Order Id'
)->addColumn(
    'state',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'State'
)->addColumn(
    'store_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Store Currency Code'
)->addColumn(
    'order_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Order Currency Code'
)->addColumn(
    'base_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Base Currency Code'
)->addColumn(
    'global_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Global Currency Code'
)->addColumn(
    'increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Increment Id'
)->addColumn(
    'order_increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Order Increment Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addColumn(
    'order_created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Order Created At'
)->addColumn(
    'billing_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Billing Name'
)->addIndex(
    $this->getIdxName('sales_invoice_grid', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_invoice_grid', ['grand_total']),
    ['grand_total']
)->addIndex(
    $this->getIdxName('sales_invoice_grid', ['order_id']),
    ['order_id']
)->addIndex(
    $this->getIdxName('sales_invoice_grid', ['state']),
    ['state']
)->addIndex(
    $this->getIdxName(
        'sales_invoice_grid',
        ['increment_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['increment_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_invoice_grid', ['order_increment_id']),
    ['order_increment_id']
)->addIndex(
    $this->getIdxName('sales_invoice_grid', ['created_at']),
    ['created_at']
)->addIndex(
    $this->getIdxName('sales_invoice_grid', ['order_created_at']),
    ['order_created_at']
)->addIndex(
    $this->getIdxName('sales_invoice_grid', ['billing_name']),
    ['billing_name']
)->addForeignKey(
    $this->getFkName('sales_invoice_grid', 'entity_id', 'sales_invoice', 'entity_id'),
    'entity_id',
    $this->getTable('sales_invoice'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_invoice_grid', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Invoice Grid'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_invoice_item'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_invoice_item')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Parent Id'
)->addColumn(
    'base_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Price'
)->addColumn(
    'tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Amount'
)->addColumn(
    'base_row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Row Total'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Amount'
)->addColumn(
    'row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Row Total'
)->addColumn(
    'base_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Discount Amount'
)->addColumn(
    'price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Price Incl Tax'
)->addColumn(
    'base_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Amount'
)->addColumn(
    'base_price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Price Incl Tax'
)->addColumn(
    'qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Qty'
)->addColumn(
    'base_cost',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Cost'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Price'
)->addColumn(
    'base_row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Row Total Incl Tax'
)->addColumn(
    'row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Row Total Incl Tax'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Product Id'
)->addColumn(
    'order_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Order Item Id'
)->addColumn(
    'additional_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Data'
)->addColumn(
    'description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Description'
)->addColumn(
    'sku',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Sku'
)->addColumn(
    'name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Name'
)->addColumn(
    'hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Amount'
)->addColumn(
    'base_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Amount'
)->addColumn(
    'tax_ratio',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    512,
    [],
    'Ratio of tax invoiced over tax of the order item'
)->addIndex(
    $this->getIdxName('sales_invoice_item', ['parent_id']),
    ['parent_id']
)->addForeignKey(
    $this->getFkName('sales_invoice_item', 'parent_id', 'sales_invoice', 'entity_id'),
    'parent_id',
    $this->getTable('sales_invoice'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Invoice Item'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_invoice_comment'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_invoice_comment')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Parent Id'
)->addColumn(
    'is_customer_notified',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Customer Notified'
)->addColumn(
    'is_visible_on_front',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Is Visible On Front'
)->addColumn(
    'comment',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Comment'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addIndex(
    $this->getIdxName('sales_invoice_comment', ['created_at']),
    ['created_at']
)->addIndex(
    $this->getIdxName('sales_invoice_comment', ['parent_id']),
    ['parent_id']
)->addForeignKey(
    $this->getFkName('sales_invoice_comment', 'parent_id', 'sales_invoice', 'entity_id'),
    'parent_id',
    $this->getTable('sales_invoice'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Invoice Comment'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_creditmemo'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_creditmemo')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'adjustment_positive',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Adjustment Positive'
)->addColumn(
    'base_shipping_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Tax Amount'
)->addColumn(
    'store_to_order_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Store To Order Rate'
)->addColumn(
    'base_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Discount Amount'
)->addColumn(
    'base_to_order_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Order Rate'
)->addColumn(
    'grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Grand Total'
)->addColumn(
    'base_adjustment_negative',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Adjustment Negative'
)->addColumn(
    'base_subtotal_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal Incl Tax'
)->addColumn(
    'shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Amount'
)->addColumn(
    'subtotal_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal Incl Tax'
)->addColumn(
    'adjustment_negative',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Adjustment Negative'
)->addColumn(
    'base_shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Amount'
)->addColumn(
    'store_to_base_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Store To Base Rate'
)->addColumn(
    'base_to_global_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Global Rate'
)->addColumn(
    'base_adjustment',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Adjustment'
)->addColumn(
    'base_subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Amount'
)->addColumn(
    'subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal'
)->addColumn(
    'adjustment',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Adjustment'
)->addColumn(
    'base_grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Grand Total'
)->addColumn(
    'base_adjustment_positive',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Adjustment Positive'
)->addColumn(
    'base_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Amount'
)->addColumn(
    'shipping_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Tax Amount'
)->addColumn(
    'tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Amount'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Order Id'
)->addColumn(
    'email_sent',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Email Sent'
)->addColumn(
    'creditmemo_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Creditmemo Status'
)->addColumn(
    'state',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'State'
)->addColumn(
    'shipping_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Shipping Address Id'
)->addColumn(
    'billing_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Billing Address Id'
)->addColumn(
    'invoice_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Invoice Id'
)->addColumn(
    'store_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Store Currency Code'
)->addColumn(
    'order_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Order Currency Code'
)->addColumn(
    'base_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Base Currency Code'
)->addColumn(
    'global_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Global Currency Code'
)->addColumn(
    'transaction_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Transaction Id'
)->addColumn(
    'increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Increment Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Updated At'
)->addColumn(
    'hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Amount'
)->addColumn(
    'base_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Amount'
)->addColumn(
    'shipping_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Hidden Tax Amount'
)->addColumn(
    'base_shipping_hidden_tax_amnt',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Hidden Tax Amount'
)->addColumn(
    'shipping_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Incl Tax'
)->addColumn(
    'base_shipping_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Incl Tax'
)->addColumn(
    'discount_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Discount Description'
)->addIndex(
    $this->getIdxName('sales_creditmemo', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_creditmemo', ['order_id']),
    ['order_id']
)->addIndex(
    $this->getIdxName('sales_creditmemo', ['creditmemo_status']),
    ['creditmemo_status']
)->addIndex(
    $this->getIdxName(
        'sales_creditmemo',
        ['increment_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['increment_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_creditmemo', ['state']),
    ['state']
)->addIndex(
    $this->getIdxName('sales_creditmemo', ['created_at']),
    ['created_at']
)->addForeignKey(
    $this->getFkName('sales_creditmemo', 'order_id', 'sales_order', 'entity_id'),
    'order_id',
    $this->getTable('sales_order'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_creditmemo', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Creditmemo'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_creditmemo_grid'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_creditmemo_grid')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'store_to_order_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Store To Order Rate'
)->addColumn(
    'base_to_order_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Order Rate'
)->addColumn(
    'grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Grand Total'
)->addColumn(
    'store_to_base_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Store To Base Rate'
)->addColumn(
    'base_to_global_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Global Rate'
)->addColumn(
    'base_grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Grand Total'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Order Id'
)->addColumn(
    'creditmemo_status',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Creditmemo Status'
)->addColumn(
    'state',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'State'
)->addColumn(
    'invoice_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Invoice Id'
)->addColumn(
    'store_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Store Currency Code'
)->addColumn(
    'order_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Order Currency Code'
)->addColumn(
    'base_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Base Currency Code'
)->addColumn(
    'global_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    3,
    [],
    'Global Currency Code'
)->addColumn(
    'increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Increment Id'
)->addColumn(
    'order_increment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    50,
    [],
    'Order Increment Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addColumn(
    'order_created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Order Created At'
)->addColumn(
    'billing_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Billing Name'
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['grand_total']),
    ['grand_total']
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['base_grand_total']),
    ['base_grand_total']
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['order_id']),
    ['order_id']
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['creditmemo_status']),
    ['creditmemo_status']
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['state']),
    ['state']
)->addIndex(
    $this->getIdxName(
        'sales_creditmemo_grid',
        ['increment_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['increment_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['order_increment_id']),
    ['order_increment_id']
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['created_at']),
    ['created_at']
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['order_created_at']),
    ['order_created_at']
)->addIndex(
    $this->getIdxName('sales_creditmemo_grid', ['billing_name']),
    ['billing_name']
)->addForeignKey(
    $this->getFkName('sales_creditmemo_grid', 'entity_id', 'sales_creditmemo', 'entity_id'),
    'entity_id',
    $this->getTable('sales_creditmemo'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_creditmemo_grid', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Creditmemo Grid'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_creditmemo_item'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_creditmemo_item')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Parent Id'
)->addColumn(
    'base_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Price'
)->addColumn(
    'tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Amount'
)->addColumn(
    'base_row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Row Total'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Amount'
)->addColumn(
    'row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Row Total'
)->addColumn(
    'base_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Discount Amount'
)->addColumn(
    'price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Price Incl Tax'
)->addColumn(
    'base_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Amount'
)->addColumn(
    'base_price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Price Incl Tax'
)->addColumn(
    'qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Qty'
)->addColumn(
    'base_cost',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Cost'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Price'
)->addColumn(
    'base_row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Row Total Incl Tax'
)->addColumn(
    'row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Row Total Incl Tax'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Product Id'
)->addColumn(
    'order_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Order Item Id'
)->addColumn(
    'additional_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Data'
)->addColumn(
    'description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Description'
)->addColumn(
    'sku',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Sku'
)->addColumn(
    'name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Name'
)->addColumn(
    'hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Amount'
)->addColumn(
    'base_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Amount'
)->addColumn(
    'tax_ratio',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    512,
    [],
    'Ratio of tax in the creditmemo item over tax of the order item'
)->addIndex(
    $this->getIdxName('sales_creditmemo_item', ['parent_id']),
    ['parent_id']
)->addForeignKey(
    $this->getFkName('sales_creditmemo_item', 'parent_id', 'sales_creditmemo', 'entity_id'),
    'parent_id',
    $this->getTable('sales_creditmemo'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Creditmemo Item'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_creditmemo_comment'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_creditmemo_comment')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Parent Id'
)->addColumn(
    'is_customer_notified',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    [],
    'Is Customer Notified'
)->addColumn(
    'is_visible_on_front',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Is Visible On Front'
)->addColumn(
    'comment',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Comment'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addIndex(
    $this->getIdxName('sales_creditmemo_comment', ['created_at']),
    ['created_at']
)->addIndex(
    $this->getIdxName('sales_creditmemo_comment', ['parent_id']),
    ['parent_id']
)->addForeignKey(
    $this->getFkName('sales_creditmemo_comment', 'parent_id', 'sales_creditmemo', 'entity_id'),
    'parent_id',
    $this->getTable('sales_creditmemo'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Creditmemo Comment'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_quote'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_quote')
)->addColumn(
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Entity Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Store Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
    'Updated At'
)->addColumn(
    'converted_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => true],
    'Converted At'
)->addColumn(
    'is_active',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '1'],
    'Is Active'
)->addColumn(
    'is_virtual',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Is Virtual'
)->addColumn(
    'is_multi_shipping',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Is Multi Shipping'
)->addColumn(
    'items_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Items Count'
)->addColumn(
    'items_qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Items Qty'
)->addColumn(
    'orig_order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Orig Order Id'
)->addColumn(
    'store_to_base_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Store To Base Rate'
)->addColumn(
    'store_to_quote_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Store To Quote Rate'
)->addColumn(
    'base_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Base Currency Code'
)->addColumn(
    'store_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Store Currency Code'
)->addColumn(
    'quote_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Quote Currency Code'
)->addColumn(
    'grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Grand Total'
)->addColumn(
    'base_grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Grand Total'
)->addColumn(
    'checkout_method',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Checkout Method'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Customer Id'
)->addColumn(
    'customer_tax_class_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Customer Tax Class Id'
)->addColumn(
    'customer_group_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Customer Group Id'
)->addColumn(
    'customer_email',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Email'
)->addColumn(
    'customer_prefix',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    40,
    [],
    'Customer Prefix'
)->addColumn(
    'customer_firstname',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Firstname'
)->addColumn(
    'customer_middlename',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    40,
    [],
    'Customer Middlename'
)->addColumn(
    'customer_lastname',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Lastname'
)->addColumn(
    'customer_suffix',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    40,
    [],
    'Customer Suffix'
)->addColumn(
    'customer_dob',
    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
    null,
    [],
    'Customer Dob'
)->addColumn(
    'customer_note',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Note'
)->addColumn(
    'customer_note_notify',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '1'],
    'Customer Note Notify'
)->addColumn(
    'customer_is_guest',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Customer Is Guest'
)->addColumn(
    'remote_ip',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    [],
    'Remote Ip'
)->addColumn(
    'applied_rule_ids',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Applied Rule Ids'
)->addColumn(
    'reserved_order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    64,
    ['nullable' => true],
    'Reserved Order Id'
)->addColumn(
    'password_hash',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Password Hash'
)->addColumn(
    'coupon_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Coupon Code'
)->addColumn(
    'global_currency_code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Global Currency Code'
)->addColumn(
    'base_to_global_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Global Rate'
)->addColumn(
    'base_to_quote_rate',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base To Quote Rate'
)->addColumn(
    'customer_taxvat',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Taxvat'
)->addColumn(
    'customer_gender',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Customer Gender'
)->addColumn(
    'subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal'
)->addColumn(
    'base_subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal'
)->addColumn(
    'subtotal_with_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal With Discount'
)->addColumn(
    'base_subtotal_with_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal With Discount'
)->addColumn(
    'is_changed',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Is Changed'
)->addColumn(
    'trigger_recollect',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false, 'default' => '0'],
    'Trigger Recollect'
)->addColumn(
    'ext_shipping_info',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Ext Shipping Info'
)->addIndex(
    $this->getIdxName('sales_quote', ['customer_id', 'store_id', 'is_active']),
    ['customer_id', 'store_id', 'is_active']
)->addIndex(
    $this->getIdxName('sales_quote', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_quote', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Quote'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_quote_address'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_quote_address')
)->addColumn(
    'address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Address Id'
)->addColumn(
    'quote_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Quote Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
    'Updated At'
)->addColumn(
    'customer_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Customer Id'
)->addColumn(
    'save_in_address_book',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['default' => '0'],
    'Save In Address Book'
)->addColumn(
    'customer_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Customer Address Id'
)->addColumn(
    'address_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Address Type'
)->addColumn(
    'email',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Email'
)->addColumn(
    'prefix',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    40,
    [],
    'Prefix'
)->addColumn(
    'firstname',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Firstname'
)->addColumn(
    'middlename',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    40,
    [],
    'Middlename'
)->addColumn(
    'lastname',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Lastname'
)->addColumn(
    'suffix',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    40,
    [],
    'Suffix'
)->addColumn(
    'company',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Company'
)->addColumn(
    'street',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Street'
)->addColumn(
    'city',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'City'
)->addColumn(
    'region',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Region'
)->addColumn(
    'region_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Region Id'
)->addColumn(
    'postcode',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Postcode'
)->addColumn(
    'country_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Country Id'
)->addColumn(
    'telephone',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Phone Number'
)->addColumn(
    'fax',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Fax'
)->addColumn(
    'same_as_billing',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Same As Billing'
)->addColumn(
    'collect_shipping_rates',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Collect Shipping Rates'
)->addColumn(
    'shipping_method',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Shipping Method'
)->addColumn(
    'shipping_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Shipping Description'
)->addColumn(
    'weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Weight'
)->addColumn(
    'subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Subtotal'
)->addColumn(
    'base_subtotal',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Subtotal'
)->addColumn(
    'subtotal_with_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Subtotal With Discount'
)->addColumn(
    'base_subtotal_with_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Subtotal With Discount'
)->addColumn(
    'tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Tax Amount'
)->addColumn(
    'base_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Tax Amount'
)->addColumn(
    'shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Shipping Amount'
)->addColumn(
    'base_shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Shipping Amount'
)->addColumn(
    'shipping_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Tax Amount'
)->addColumn(
    'base_shipping_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Tax Amount'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Discount Amount'
)->addColumn(
    'base_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Discount Amount'
)->addColumn(
    'grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Grand Total'
)->addColumn(
    'base_grand_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Grand Total'
)->addColumn(
    'customer_notes',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Customer Notes'
)->addColumn(
    'applied_taxes',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Applied Taxes'
)->addColumn(
    'discount_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Discount Description'
)->addColumn(
    'shipping_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Discount Amount'
)->addColumn(
    'base_shipping_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Discount Amount'
)->addColumn(
    'subtotal_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Subtotal Incl Tax'
)->addColumn(
    'base_subtotal_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Subtotal Total Incl Tax'
)->addColumn(
    'hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Amount'
)->addColumn(
    'base_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Amount'
)->addColumn(
    'shipping_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Hidden Tax Amount'
)->addColumn(
    'base_shipping_hidden_tax_amnt',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Hidden Tax Amount'
)->addColumn(
    'shipping_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Shipping Incl Tax'
)->addColumn(
    'base_shipping_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Shipping Incl Tax'
)->addIndex(
    $this->getIdxName('sales_quote_address', ['quote_id']),
    ['quote_id']
)->addForeignKey(
    $this->getFkName('sales_quote_address', 'quote_id', 'sales_quote', 'entity_id'),
    'quote_id',
    $this->getTable('sales_quote'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Quote Address'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_quote_item'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_quote_item')
)->addColumn(
    'item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Item Id'
)->addColumn(
    'quote_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Quote Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
    'Updated At'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Product Id'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'parent_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Parent Item Id'
)->addColumn(
    'is_virtual',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Virtual'
)->addColumn(
    'sku',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Sku'
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
    'applied_rule_ids',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Applied Rule Ids'
)->addColumn(
    'additional_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Data'
)->addColumn(
    'is_qty_decimal',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Is Qty Decimal'
)->addColumn(
    'no_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '0'],
    'No Discount'
)->addColumn(
    'weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Weight'
)->addColumn(
    'qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Qty'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Price'
)->addColumn(
    'base_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Price'
)->addColumn(
    'custom_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Custom Price'
)->addColumn(
    'discount_percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Discount Percent'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Discount Amount'
)->addColumn(
    'base_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Discount Amount'
)->addColumn(
    'tax_percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Tax Percent'
)->addColumn(
    'tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Tax Amount'
)->addColumn(
    'base_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Tax Amount'
)->addColumn(
    'row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Row Total'
)->addColumn(
    'base_row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Row Total'
)->addColumn(
    'row_total_with_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Row Total With Discount'
)->addColumn(
    'row_weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Row Weight'
)->addColumn(
    'product_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Product Type'
)->addColumn(
    'base_tax_before_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Tax Before Discount'
)->addColumn(
    'tax_before_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Before Discount'
)->addColumn(
    'original_custom_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Original Custom Price'
)->addColumn(
    'redirect_url',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Redirect Url'
)->addColumn(
    'base_cost',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Cost'
)->addColumn(
    'price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Price Incl Tax'
)->addColumn(
    'base_price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Price Incl Tax'
)->addColumn(
    'row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Row Total Incl Tax'
)->addColumn(
    'base_row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Row Total Incl Tax'
)->addColumn(
    'hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Amount'
)->addColumn(
    'base_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Amount'
)->addIndex(
    $this->getIdxName('sales_quote_item', ['parent_item_id']),
    ['parent_item_id']
)->addIndex(
    $this->getIdxName('sales_quote_item', ['product_id']),
    ['product_id']
)->addIndex(
    $this->getIdxName('sales_quote_item', ['quote_id']),
    ['quote_id']
)->addIndex(
    $this->getIdxName('sales_quote_item', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_quote_item', 'parent_item_id', 'sales_quote_item', 'item_id'),
    'parent_item_id',
    $this->getTable('sales_quote_item'),
    'item_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_quote_item', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $this->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_quote_item', 'quote_id', 'sales_quote', 'entity_id'),
    'quote_id',
    $this->getTable('sales_quote'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_quote_item', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Quote Item'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_quote_address_item'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_quote_address_item')
)->addColumn(
    'address_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Address Item Id'
)->addColumn(
    'parent_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Parent Item Id'
)->addColumn(
    'quote_address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Quote Address Id'
)->addColumn(
    'quote_item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Quote Item Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
    'Updated At'
)->addColumn(
    'applied_rule_ids',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Applied Rule Ids'
)->addColumn(
    'additional_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Data'
)->addColumn(
    'weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Weight'
)->addColumn(
    'qty',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Qty'
)->addColumn(
    'discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Discount Amount'
)->addColumn(
    'tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Tax Amount'
)->addColumn(
    'row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Row Total'
)->addColumn(
    'base_row_total',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Base Row Total'
)->addColumn(
    'row_total_with_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Row Total With Discount'
)->addColumn(
    'base_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Discount Amount'
)->addColumn(
    'base_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Base Tax Amount'
)->addColumn(
    'row_weight',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['default' => '0.0000'],
    'Row Weight'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Product Id'
)->addColumn(
    'super_product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Super Product Id'
)->addColumn(
    'parent_product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Parent Product Id'
)->addColumn(
    'sku',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Sku'
)->addColumn(
    'image',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Image'
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
    'is_qty_decimal',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Is Qty Decimal'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Price'
)->addColumn(
    'discount_percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Discount Percent'
)->addColumn(
    'no_discount',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'No Discount'
)->addColumn(
    'tax_percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Tax Percent'
)->addColumn(
    'base_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Price'
)->addColumn(
    'base_cost',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Cost'
)->addColumn(
    'price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Price Incl Tax'
)->addColumn(
    'base_price_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Price Incl Tax'
)->addColumn(
    'row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Row Total Incl Tax'
)->addColumn(
    'base_row_total_incl_tax',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Row Total Incl Tax'
)->addColumn(
    'hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Hidden Tax Amount'
)->addColumn(
    'base_hidden_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Hidden Tax Amount'
)->addIndex(
    $this->getIdxName('sales_quote_address_item', ['quote_address_id']),
    ['quote_address_id']
)->addIndex(
    $this->getIdxName('sales_quote_address_item', ['parent_item_id']),
    ['parent_item_id']
)->addIndex(
    $this->getIdxName('sales_quote_address_item', ['quote_item_id']),
    ['quote_item_id']
)->addForeignKey(
    $this->getFkName(
        'sales_quote_address_item',
        'quote_address_id',
        'sales_quote_address',
        'address_id'
    ),
    'quote_address_id',
    $this->getTable('sales_quote_address'),
    'address_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName(
        'sales_quote_address_item',
        'parent_item_id',
        'sales_quote_address_item',
        'address_item_id'
    ),
    'parent_item_id',
    $this->getTable('sales_quote_address_item'),
    'address_item_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_quote_address_item', 'quote_item_id', 'sales_quote_item', 'item_id'),
    'quote_item_id',
    $this->getTable('sales_quote_item'),
    'item_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Quote Address Item'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_quote_item_option'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_quote_item_option')
)->addColumn(
    'option_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Option Id'
)->addColumn(
    'item_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Item Id'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Product Id'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => false],
    'Code'
)->addColumn(
    'value',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Value'
)->addIndex(
    $this->getIdxName('sales_quote_item_option', ['item_id']),
    ['item_id']
)->addForeignKey(
    $this->getFkName('sales_quote_item_option', 'item_id', 'sales_quote_item', 'item_id'),
    'item_id',
    $this->getTable('sales_quote_item'),
    'item_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Quote Item Option'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_quote_payment'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_quote_payment')
)->addColumn(
    'payment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Payment Id'
)->addColumn(
    'quote_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Quote Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
    'Updated At'
)->addColumn(
    'method',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Method'
)->addColumn(
    'cc_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Type'
)->addColumn(
    'cc_number_enc',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Number Enc'
)->addColumn(
    'cc_last_4',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Last 4'
)->addColumn(
    'cc_cid_enc',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Cid Enc'
)->addColumn(
    'cc_owner',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Owner'
)->addColumn(
    'cc_exp_month',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['unsigned' => true, 'default' => null, 'nullable' => true],
    'Cc Exp Month'
)->addColumn(
    'cc_exp_year',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Cc Exp Year'
)->addColumn(
    'cc_ss_owner',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Ss Owner'
)->addColumn(
    'cc_ss_start_month',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Cc Ss Start Month'
)->addColumn(
    'cc_ss_start_year',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'default' => '0'],
    'Cc Ss Start Year'
)->addColumn(
    'po_number',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Po Number'
)->addColumn(
    'additional_data',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Data'
)->addColumn(
    'cc_ss_issue',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Cc Ss Issue'
)->addColumn(
    'additional_information',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Additional Information'
)->addIndex(
    $this->getIdxName('sales_quote_payment', ['quote_id']),
    ['quote_id']
)->addForeignKey(
    $this->getFkName('sales_quote_payment', 'quote_id', 'sales_quote', 'entity_id'),
    'quote_id',
    $this->getTable('sales_quote'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Quote Payment'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_quote_shipping_rate'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_quote_shipping_rate')
)->addColumn(
    'rate_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Rate Id'
)->addColumn(
    'address_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Address Id'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
    'Created At'
)->addColumn(
    'updated_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
    'Updated At'
)->addColumn(
    'carrier',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Carrier'
)->addColumn(
    'carrier_title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Carrier Title'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Code'
)->addColumn(
    'method',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Method'
)->addColumn(
    'method_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Method Description'
)->addColumn(
    'price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Price'
)->addColumn(
    'error_message',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Error Message'
)->addColumn(
    'method_title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    '64k',
    [],
    'Method Title'
)->addIndex(
    $this->getIdxName('sales_quote_shipping_rate', ['address_id']),
    ['address_id']
)->addForeignKey(
    $this->getFkName('sales_quote_shipping_rate', 'address_id', 'sales_quote_address', 'address_id'),
    'address_id',
    $this->getTable('sales_quote_address'),
    'address_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Flat Quote Shipping Rate'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_invoiced_aggregated'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_invoiced_aggregated')
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
    [],
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
    'orders_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Orders Count'
)->addColumn(
    'orders_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Orders Invoiced'
)->addColumn(
    'invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Invoiced'
)->addColumn(
    'invoiced_captured',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Invoiced Captured'
)->addColumn(
    'invoiced_not_captured',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Invoiced Not Captured'
)->addIndex(
    $this->getIdxName(
        'sales_invoiced_aggregated',
        ['period', 'store_id', 'order_status'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'order_status'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_invoiced_aggregated', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_invoiced_aggregated', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Invoiced Aggregated'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_invoiced_aggregated_order'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_invoiced_aggregated_order')
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
    [],
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
    ['nullable' => false, 'default' => false],
    'Order Status'
)->addColumn(
    'orders_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Orders Count'
)->addColumn(
    'orders_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Orders Invoiced'
)->addColumn(
    'invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Invoiced'
)->addColumn(
    'invoiced_captured',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Invoiced Captured'
)->addColumn(
    'invoiced_not_captured',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Invoiced Not Captured'
)->addIndex(
    $this->getIdxName(
        'sales_invoiced_aggregated_order',
        ['period', 'store_id', 'order_status'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'order_status'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_invoiced_aggregated_order', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_invoiced_aggregated_order', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Invoiced Aggregated Order'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_aggregated_created'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_aggregated_created')
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
    [],
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
    ['nullable' => false, 'default' => false],
    'Order Status'
)->addColumn(
    'orders_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Orders Count'
)->addColumn(
    'total_qty_ordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Qty Ordered'
)->addColumn(
    'total_qty_invoiced',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Qty Invoiced'
)->addColumn(
    'total_income_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Income Amount'
)->addColumn(
    'total_revenue_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Revenue Amount'
)->addColumn(
    'total_profit_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Profit Amount'
)->addColumn(
    'total_invoiced_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Invoiced Amount'
)->addColumn(
    'total_canceled_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Canceled Amount'
)->addColumn(
    'total_paid_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Paid Amount'
)->addColumn(
    'total_refunded_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Refunded Amount'
)->addColumn(
    'total_tax_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Tax Amount'
)->addColumn(
    'total_tax_amount_actual',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Tax Amount Actual'
)->addColumn(
    'total_shipping_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Shipping Amount'
)->addColumn(
    'total_shipping_amount_actual',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Shipping Amount Actual'
)->addColumn(
    'total_discount_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Discount Amount'
)->addColumn(
    'total_discount_amount_actual',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Total Discount Amount Actual'
)->addIndex(
    $this->getIdxName(
        'sales_order_aggregated_created',
        ['period', 'store_id', 'order_status'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'order_status'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_order_aggregated_created', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_order_aggregated_created', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Order Aggregated Created'
);
$this->getConnection()->createTable($table);

$this->getConnection()->createTable(
    $this->getConnection()->createTableByDdl(
        $this->getTable('sales_order_aggregated_created'),
        $this->getTable('sales_order_aggregated_updated')
    )
);

/**
 * Create table 'sales_payment_transaction'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_payment_transaction')
)->addColumn(
    'transaction_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Transaction Id'
)->addColumn(
    'parent_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Parent Id'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Order Id'
)->addColumn(
    'payment_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Payment Id'
)->addColumn(
    'txn_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    100,
    [],
    'Txn Id'
)->addColumn(
    'parent_txn_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    100,
    [],
    'Parent Txn Id'
)->addColumn(
    'txn_type',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    15,
    [],
    'Txn Type'
)->addColumn(
    'is_closed',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '1'],
    'Is Closed'
)->addColumn(
    'additional_information',
    \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
    '64K',
    [],
    'Additional Information'
)->addColumn(
    'created_at',
    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
    null,
    [],
    'Created At'
)->addIndex(
    $this->getIdxName(
        'sales_payment_transaction',
        ['order_id', 'payment_id', 'txn_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['order_id', 'payment_id', 'txn_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_payment_transaction', ['parent_id']),
    ['parent_id']
)->addIndex(
    $this->getIdxName('sales_payment_transaction', ['payment_id']),
    ['payment_id']
)->addForeignKey(
    $this->getFkName('sales_payment_transaction', 'order_id', 'sales_order', 'entity_id'),
    'order_id',
    $this->getTable('sales_order'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_payment_transaction', 'parent_id', 'sales_payment_transaction', 'transaction_id'),
    'parent_id',
    $this->getTable('sales_payment_transaction'),
    'transaction_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_payment_transaction', 'payment_id', 'sales_order_payment', 'entity_id'),
    'payment_id',
    $this->getTable('sales_order_payment'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Payment Transaction'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_refunded_aggregated'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_refunded_aggregated')
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
    [],
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
    ['nullable' => false, 'default' => false],
    'Order Status'
)->addColumn(
    'orders_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Orders Count'
)->addColumn(
    'refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Refunded'
)->addColumn(
    'online_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Online Refunded'
)->addColumn(
    'offline_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Offline Refunded'
)->addIndex(
    $this->getIdxName(
        'sales_refunded_aggregated',
        ['period', 'store_id', 'order_status'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'order_status'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_refunded_aggregated', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_refunded_aggregated', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Refunded Aggregated'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_refunded_aggregated_order'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_refunded_aggregated_order')
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
    [],
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
    'orders_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Orders Count'
)->addColumn(
    'refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Refunded'
)->addColumn(
    'online_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Online Refunded'
)->addColumn(
    'offline_refunded',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Offline Refunded'
)->addIndex(
    $this->getIdxName(
        'sales_refunded_aggregated_order',
        ['period', 'store_id', 'order_status'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'order_status'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_refunded_aggregated_order', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_refunded_aggregated_order', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Refunded Aggregated Order'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_shipping_aggregated'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_shipping_aggregated')
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
    [],
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
    'shipping_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Shipping Description'
)->addColumn(
    'orders_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Orders Count'
)->addColumn(
    'total_shipping',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Shipping'
)->addColumn(
    'total_shipping_actual',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Shipping Actual'
)->addIndex(
    $this->getIdxName(
        'sales_shipping_aggregated',
        ['period', 'store_id', 'order_status', 'shipping_description'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'order_status', 'shipping_description'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_shipping_aggregated', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_shipping_aggregated', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Shipping Aggregated'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_shipping_aggregated_order'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_shipping_aggregated_order')
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
    [],
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
    'shipping_description',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Shipping Description'
)->addColumn(
    'orders_count',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['nullable' => false, 'default' => '0'],
    'Orders Count'
)->addColumn(
    'total_shipping',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Shipping'
)->addColumn(
    'total_shipping_actual',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Total Shipping Actual'
)->addIndex(
    $this->getIdxName(
        'sales_shipping_aggregated_order',
        ['period', 'store_id', 'order_status', 'shipping_description'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'order_status', 'shipping_description'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_shipping_aggregated_order', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_shipping_aggregated_order', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Shipping Aggregated Order'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_bestsellers_aggregated_daily'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_bestsellers_aggregated_daily')
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
    [],
    'Period'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Product Id'
)->addColumn(
    'product_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true],
    'Product Name'
)->addColumn(
    'product_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Product Price'
)->addColumn(
    'qty_ordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Qty Ordered'
)->addColumn(
    'rating_pos',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Rating Pos'
)->addIndex(
    $this->getIdxName(
        'sales_bestsellers_aggregated_daily',
        ['period', 'store_id', 'product_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'product_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_bestsellers_aggregated_daily', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_bestsellers_aggregated_daily', ['product_id']),
    ['product_id']
)->addForeignKey(
    $this->getFkName('sales_bestsellers_aggregated_daily', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_bestsellers_aggregated_daily', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $this->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Bestsellers Aggregated Daily'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_bestsellers_aggregated_monthly'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_bestsellers_aggregated_monthly')
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
    [],
    'Period'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Product Id'
)->addColumn(
    'product_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true],
    'Product Name'
)->addColumn(
    'product_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Product Price'
)->addColumn(
    'qty_ordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Qty Ordered'
)->addColumn(
    'rating_pos',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Rating Pos'
)->addIndex(
    $this->getIdxName(
        'sales_bestsellers_aggregated_monthly',
        ['period', 'store_id', 'product_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'product_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_bestsellers_aggregated_monthly', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_bestsellers_aggregated_monthly', ['product_id']),
    ['product_id']
)->addForeignKey(
    $this->getFkName('sales_bestsellers_aggregated_monthly', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_bestsellers_aggregated_monthly', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $this->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Bestsellers Aggregated Monthly'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_bestsellers_aggregated_yearly'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_bestsellers_aggregated_yearly')
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
    [],
    'Period'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true],
    'Store Id'
)->addColumn(
    'product_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true],
    'Product Id'
)->addColumn(
    'product_name',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    ['nullable' => true],
    'Product Name'
)->addColumn(
    'product_price',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Product Price'
)->addColumn(
    'qty_ordered',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    ['nullable' => false, 'default' => '0.0000'],
    'Qty Ordered'
)->addColumn(
    'rating_pos',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Rating Pos'
)->addIndex(
    $this->getIdxName(
        'sales_bestsellers_aggregated_yearly',
        ['period', 'store_id', 'product_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
    ),
    ['period', 'store_id', 'product_id'],
    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
)->addIndex(
    $this->getIdxName('sales_bestsellers_aggregated_yearly', ['store_id']),
    ['store_id']
)->addIndex(
    $this->getIdxName('sales_bestsellers_aggregated_yearly', ['product_id']),
    ['product_id']
)->addForeignKey(
    $this->getFkName('sales_bestsellers_aggregated_yearly', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_bestsellers_aggregated_yearly', 'product_id', 'catalog_product_entity', 'entity_id'),
    'product_id',
    $this->getTable('catalog_product_entity'),
    'entity_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Bestsellers Aggregated Yearly'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_tax'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_tax')
)->addColumn(
    'tax_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
    'Tax Id'
)->addColumn(
    'order_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    null,
    ['unsigned' => true, 'nullable' => false],
    'Order Id'
)->addColumn(
    'code',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Code'
)->addColumn(
    'title',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    255,
    [],
    'Title'
)->addColumn(
    'percent',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Percent'
)->addColumn(
    'amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Amount'
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
    'base_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Amount'
)->addColumn(
    'process',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['nullable' => false],
    'Process'
)->addColumn(
    'base_real_amount',
    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
    '12,4',
    [],
    'Base Real Amount'
)->addColumn(
    'hidden',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Hidden'
)->addIndex(
    $this->getIdxName('sales_order_tax', ['order_id', 'priority', 'position']),
    ['order_id', 'priority', 'position']
)->setComment(
    'Sales Order Tax Table'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_status'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_status')
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => false, 'primary' => true],
    'Status'
)->addColumn(
    'label',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    128,
    ['nullable' => false],
    'Label'
)->setComment(
    'Sales Order Status Table'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_status_state'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_status_state')
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => false, 'primary' => true],
    'Status'
)->addColumn(
    'state',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => false, 'primary' => true],
    'Label'
)->addColumn(
    'is_default',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Is Default'
)->addColumn(
    'visible_on_front',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    1,
    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
    'Visible on front'
)->addForeignKey(
    $this->getFkName('sales_order_status_state', 'status', 'sales_order_status', 'status'),
    'status',
    $this->getTable('sales_order_status'),
    'status',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Order Status Table'
);
$this->getConnection()->createTable($table);

/**
 * Create table 'sales_order_status_label'
 */
$table = $this->getConnection()->newTable(
    $this->getTable('sales_order_status_label')
)->addColumn(
    'status',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    32,
    ['nullable' => false, 'primary' => true],
    'Status'
)->addColumn(
    'store_id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['unsigned' => true, 'nullable' => false, 'primary' => true],
    'Store Id'
)->addColumn(
    'label',
    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    128,
    ['nullable' => false],
    'Label'
)->addIndex(
    $this->getIdxName('sales_order_status_label', ['store_id']),
    ['store_id']
)->addForeignKey(
    $this->getFkName('sales_order_status_label', 'status', 'sales_order_status', 'status'),
    'status',
    $this->getTable('sales_order_status'),
    'status',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->addForeignKey(
    $this->getFkName('sales_order_status_label', 'store_id', 'store', 'store_id'),
    'store_id',
    $this->getTable('store'),
    'store_id',
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
)->setComment(
    'Sales Order Status Label Table'
);
$this->getConnection()->createTable($table);

$this->endSetup();
