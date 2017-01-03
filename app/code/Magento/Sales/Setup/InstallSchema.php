<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'sales_order'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order')
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
            'send_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Send Email'
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
            32,
            [],
            'Increment Id'
        )->addColumn(
            'applied_rule_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
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
            128,
            [],
            'Customer Email'
        )->addColumn(
            'customer_firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Customer Firstname'
        )->addColumn(
            'customer_lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Customer Lastname'
        )->addColumn(
            'customer_middlename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Customer Middlename'
        )->addColumn(
            'customer_prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Customer Prefix'
        )->addColumn(
            'customer_suffix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Customer Suffix'
        )->addColumn(
            'customer_taxvat',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
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
            32,
            [],
            'Ext Customer Id'
        )->addColumn(
            'ext_order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
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
            32,
            [],
            'Hold Before State'
        )->addColumn(
            'hold_before_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Hold Before Status'
        )->addColumn(
            'order_currency_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            [],
            'Order Currency Code'
        )->addColumn(
            'original_increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
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
            32,
            [],
            'Remote Ip'
        )->addColumn(
            'shipping_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
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
            32,
            [],
            'Store Name'
        )->addColumn(
            'x_forwarded_for',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
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
            'discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Amount'
        )->addColumn(
            'base_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Amount'
        )->addColumn(
            'shipping_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Discount Tax Compensation Amount'
        )->addColumn(
            'base_shipping_discount_tax_compensation_amnt',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Discount Tax Compensation Amount'
        )->addColumn(
            'discount_tax_compensation_invoiced',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Invoiced'
        )->addColumn(
            'base_discount_tax_compensation_invoiced',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Invoiced'
        )->addColumn(
            'discount_tax_compensation_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Refunded'
        )->addColumn(
            'base_discount_tax_compensation_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Refunded'
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
            $installer->getIdxName('sales_order', ['status']),
            ['status']
        )->addIndex(
            $installer->getIdxName('sales_order', ['state']),
            ['state']
        )->addIndex(
            $installer->getIdxName('sales_order', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName(
                'sales_order',
                ['increment_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_order', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_order', ['customer_id']),
            ['customer_id']
        )->addIndex(
            $installer->getIdxName('sales_order', ['ext_order_id']),
            ['ext_order_id']
        )->addIndex(
            $installer->getIdxName('sales_order', ['quote_id']),
            ['quote_id']
        )->addIndex(
            $installer->getIdxName('sales_order', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName('sales_order', ['send_email']),
            ['send_email']
        )->addIndex(
            $installer->getIdxName('sales_order', ['email_sent']),
            ['email_sent']
        )->addForeignKey(
            $installer->getFkName('sales_order', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName('sales_order', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Flat Order'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_grid'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_grid')
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
        )->addColumn(
            'billing_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Billing Address'
        )->addColumn(
            'shipping_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Address'
        )->addColumn(
            'shipping_information',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Method Name'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Email'
        )->addColumn(
            'customer_group',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Group'
        )->addColumn(
            'subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subtotal'
        )->addColumn(
            'shipping_and_handling',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping and handling amount'
        )->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Name'
        )->addColumn(
            'payment_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Payment Method'
        )->addColumn(
            'total_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Total Refunded'
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['status']),
            ['status']
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['base_grand_total']),
            ['base_grand_total']
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['base_total_paid']),
            ['base_total_paid']
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['grand_total']),
            ['grand_total']
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['total_paid']),
            ['total_paid']
        )->addIndex(
            $installer->getIdxName(
                'sales_order_grid',
                ['increment_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['shipping_name']),
            ['shipping_name']
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['billing_name']),
            ['billing_name']
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['customer_id']),
            ['customer_id']
        )->addIndex(
            $installer->getIdxName('sales_order_grid', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName(
                'sales_order_grid',
                [
                    'increment_id',
                    'billing_name',
                    'shipping_name',
                    'shipping_address',
                    'billing_address',
                    'customer_name',
                    'customer_email'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            [
                'increment_id',
                'billing_name',
                'shipping_name',
                'shipping_address',
                'billing_address',
                'customer_name',
                'customer_email'
            ],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Sales Flat Order Grid'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_address')
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
            $installer->getIdxName('sales_order_address', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('sales_order_address', 'parent_id', 'sales_order', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Order Address'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_status_history'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_status_history')
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'entity_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => true],
            'Shows what entity history is bind to.'
        )->addIndex(
            $installer->getIdxName('sales_order_status_history', ['parent_id']),
            ['parent_id']
        )->addIndex(
            $installer->getIdxName('sales_order_status_history', ['created_at']),
            ['created_at']
        )->addForeignKey(
            $installer->getFkName('sales_order_status_history', 'parent_id', 'sales_order', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Order Status History'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_item')
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
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
            'discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Amount'
        )->addColumn(
            'base_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Amount'
        )->addColumn(
            'discount_tax_compensation_invoiced',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Invoiced'
        )->addColumn(
            'base_discount_tax_compensation_invoiced',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Invoiced'
        )->addColumn(
            'discount_tax_compensation_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Refunded'
        )->addColumn(
            'base_discount_tax_compensation_refunded',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Refunded'
        )->addColumn(
            'tax_canceled',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Tax Canceled'
        )->addColumn(
            'discount_tax_compensation_canceled',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Canceled'
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
            $installer->getIdxName('sales_order_item', ['order_id']),
            ['order_id']
        )->addIndex(
            $installer->getIdxName('sales_order_item', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('sales_order_item', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('sales_order_item', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Flat Order Item'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_payment'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_payment')
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
            12,
            [],
            'Cc Exp Month'
        )->addColumn(
            'cc_ss_start_year',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            12,
            [],
            'Cc Ss Start Year'
        )->addColumn(
            'echeck_bank_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Echeck Bank Name'
        )->addColumn(
            'method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Method'
        )->addColumn(
            'cc_debug_request_body',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Debug Request Body'
        )->addColumn(
            'cc_secure_verify',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Secure Verify'
        )->addColumn(
            'protection_eligibility',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Protection Eligibility'
        )->addColumn(
            'cc_approval',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Approval'
        )->addColumn(
            'cc_last_4',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'Cc Last 4'
        )->addColumn(
            'cc_status_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Status Description'
        )->addColumn(
            'echeck_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Echeck Type'
        )->addColumn(
            'cc_debug_response_serialized',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Debug Response Serialized'
        )->addColumn(
            'cc_ss_start_month',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
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
            32,
            [],
            'Last Trans Id'
        )->addColumn(
            'cc_cid_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Cid Status'
        )->addColumn(
            'cc_owner',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Cc Owner'
        )->addColumn(
            'cc_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Type'
        )->addColumn(
            'po_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Po Number'
        )->addColumn(
            'cc_exp_year',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            4,
            ['nullable' => true, 'default' => null],
            'Cc Exp Year'
        )->addColumn(
            'cc_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            4,
            [],
            'Cc Status'
        )->addColumn(
            'echeck_routing_number',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Echeck Routing Number'
        )->addColumn(
            'account_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Account Status'
        )->addColumn(
            'anet_trans_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Anet Trans Method'
        )->addColumn(
            'cc_debug_response_body',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Debug Response Body'
        )->addColumn(
            'cc_ss_issue',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Ss Issue'
        )->addColumn(
            'echeck_account_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Echeck Account Name'
        )->addColumn(
            'cc_avs_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Avs Status'
        )->addColumn(
            'cc_number_enc',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Cc Number Enc'
        )->addColumn(
            'cc_trans_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Cc Trans Id'
        )->addColumn(
            'address_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Address Status'
        )->addColumn(
            'additional_information',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Additional Information'
        )->addIndex(
            $installer->getIdxName('sales_order_payment', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('sales_order_payment', 'parent_id', 'sales_order', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Order Payment'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_shipment'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_shipment')
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
            'send_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Send Email'
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
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
        )->addColumn(
            'customer_note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Customer Note'
        )->addColumn(
            'customer_note_notify',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Customer Note Notify'
        )->addIndex(
            $installer->getIdxName('sales_shipment', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('sales_shipment', ['total_qty']),
            ['total_qty']
        )->addIndex(
            $installer->getIdxName(
                'sales_shipment',
                ['increment_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_shipment', ['order_id']),
            ['order_id']
        )->addIndex(
            $installer->getIdxName('sales_shipment', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_shipment', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName('sales_shipment', ['send_email']),
            ['send_email']
        )->addIndex(
            $installer->getIdxName('sales_shipment', ['email_sent']),
            ['email_sent']
        )->addForeignKey(
            $installer->getFkName('sales_shipment', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('sales_shipment', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Flat Shipment'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_shipment_grid'
         *
         * @add order_id, shipping_description
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_shipment_grid')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Increment Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'order_increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false],
            'Order Increment Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Order Id'
        )->addColumn(
            'order_created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'Order Increment Id'
        )->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            ['nullable' => false],
            'Customer Name'
        )->addColumn(
            'total_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Total Qty'
        )->addColumn(
            'shipment_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Shipment Status'
        )->addColumn(
            'order_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Order'
        )->addColumn(
            'billing_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Billing Address'
        )->addColumn(
            'shipping_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Address'
        )->addColumn(
            'billing_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Billing Name'
        )->addColumn(
            'shipping_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Shipping Name'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Customer Email'
        )->addColumn(
            'customer_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'Customer Group Id'
        )->addColumn(
            'payment_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Payment Method'
        )->addColumn(
            'shipping_information',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Method Name'
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
            $installer->getIdxName(
                'sales_shipment_grid',
                [
                    'increment_id',
                    'store_id'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName(
                'sales_shipment_grid',
                ['store_id']
            ),
            ['store_id']
        )->addIndex(
            $installer->getIdxName(
                'sales_shipment_grid',
                ['total_qty']
            ),
            ['total_qty']
        )->addIndex(
            $installer->getIdxName(
                'sales_shipment_grid',
                ['order_increment_id']
            ),
            ['order_increment_id']
        )->addIndex(
            $installer->getIdxName(
                'sales_shipment_grid',
                ['shipment_status']
            ),
            ['shipment_status']
        )->addIndex(
            $installer->getIdxName(
                'sales_shipment_grid',
                ['order_status']
            ),
            ['order_status']
        )->addIndex(
            $installer->getIdxName('sales_shipment_grid', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_shipment_grid', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName('sales_shipment_grid', ['order_created_at']),
            ['order_created_at']
        )->addIndex(
            $installer->getIdxName('sales_shipment_grid', ['shipping_name']),
            ['shipping_name']
        )->addIndex(
            $installer->getIdxName('sales_shipment_grid', ['billing_name']),
            ['billing_name']
        )->addIndex(
            $installer->getIdxName(
                'sales_shipment_grid',
                [
                    'increment_id',
                    'order_increment_id',
                    'shipping_name',
                    'customer_name',
                    'customer_email',
                    'billing_address',
                    'shipping_address'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            [
                'increment_id',
                'order_increment_id',
                'shipping_name',
                'customer_name',
                'customer_email',
                'billing_address',
                'shipping_address'
            ],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Sales Flat Shipment Grid'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_shipment_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_shipment_item')
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
            $installer->getIdxName('sales_shipment_item', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('sales_shipment_item', 'parent_id', 'sales_shipment', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_shipment'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Shipment Item'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_shipment_track'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_shipment_track')
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addIndex(
            $installer->getIdxName('sales_shipment_track', ['parent_id']),
            ['parent_id']
        )->addIndex(
            $installer->getIdxName('sales_shipment_track', ['order_id']),
            ['order_id']
        )->addIndex(
            $installer->getIdxName('sales_shipment_track', ['created_at']),
            ['created_at']
        )->addForeignKey(
            $installer->getFkName('sales_shipment_track', 'parent_id', 'sales_shipment', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_shipment'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Shipment Track'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_shipment_comment'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_shipment_comment')
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $installer->getIdxName('sales_shipment_comment', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_shipment_comment', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('sales_shipment_comment', 'parent_id', 'sales_shipment', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_shipment'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Shipment Comment'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_invoice'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_invoice')
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
            'send_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Send Email'
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            'discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Amount'
        )->addColumn(
            'base_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Amount'
        )->addColumn(
            'shipping_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Discount Tax Compensation Amount'
        )->addColumn(
            'base_shipping_discount_tax_compensation_amnt',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Discount Tax Compensation Amount'
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
        )->addColumn(
            'customer_note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Customer Note'
        )->addColumn(
            'customer_note_notify',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Customer Note Notify'
        )->addIndex(
            $installer->getIdxName('sales_invoice', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('sales_invoice', ['grand_total']),
            ['grand_total']
        )->addIndex(
            $installer->getIdxName('sales_invoice', ['order_id']),
            ['order_id']
        )->addIndex(
            $installer->getIdxName('sales_invoice', ['state']),
            ['state']
        )->addIndex(
            $installer->getIdxName(
                'sales_invoice',
                ['increment_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_invoice', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_invoice', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName('sales_invoice', ['send_email']),
            ['send_email']
        )->addIndex(
            $installer->getIdxName('sales_invoice', ['email_sent']),
            ['email_sent']
        )->addForeignKey(
            $installer->getFkName('sales_invoice', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('sales_invoice', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Flat Invoice'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_invoice_grid'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_invoice_grid')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Increment Id'
        )->addColumn(
            'state',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'State'
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
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Order Id'
        )->addColumn(
            'order_increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Order Increment Id'
        )->addColumn(
            'order_created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Order Created At'
        )->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Name'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Email'
        )->addColumn(
            'customer_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'Customer Group Id'
        )->addColumn(
            'payment_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Payment Method'
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
            'billing_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Billing Name'
        )->addColumn(
            'billing_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Billing Address'
        )->addColumn(
            'shipping_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Address'
        )->addColumn(
            'shipping_information',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Method Name'
        )->addColumn(
            'subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subtotal'
        )->addColumn(
            'shipping_and_handling',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping and handling amount'
        )->addColumn(
            'grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Grand Total'
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
            $installer->getIdxName('sales_invoice_grid', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('sales_invoice_grid', ['grand_total']),
            ['grand_total']
        )->addIndex(
            $installer->getIdxName('sales_invoice_grid', ['order_id']),
            ['order_id']
        )->addIndex(
            $installer->getIdxName('sales_invoice_grid', ['state']),
            ['state']
        )->addIndex(
            $installer->getIdxName(
                'sales_invoice_grid',
                ['increment_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_invoice_grid', ['order_increment_id']),
            ['order_increment_id']
        )->addIndex(
            $installer->getIdxName('sales_invoice_grid', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_invoice_grid', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName('sales_invoice_grid', ['order_created_at']),
            ['order_created_at']
        )->addIndex(
            $installer->getIdxName('sales_invoice_grid', ['billing_name']),
            ['billing_name']
        )->addIndex(
            $installer->getIdxName(
                'sales_invoice_grid',
                [
                    'increment_id',
                    'order_increment_id',
                    'billing_name',
                    'billing_address',
                    'shipping_address',
                    'customer_name',
                    'customer_email'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            [
                'increment_id',
                'order_increment_id',
                'billing_name',
                'billing_address',
                'shipping_address',
                'customer_name',
                'customer_email'
            ],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Sales Flat Invoice Grid'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_invoice_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_invoice_item')
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
            'discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Amount'
        )->addColumn(
            'base_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Amount'
        )->addColumn(
            'tax_ratio',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            512,
            [],
            'Ratio of tax invoiced over tax of the order item'
        )->addIndex(
            $installer->getIdxName('sales_invoice_item', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('sales_invoice_item', 'parent_id', 'sales_invoice', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_invoice'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Invoice Item'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_invoice_comment'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_invoice_comment')
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $installer->getIdxName('sales_invoice_comment', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_invoice_comment', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('sales_invoice_comment', 'parent_id', 'sales_invoice', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_invoice'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Invoice Comment'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_creditmemo'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_creditmemo')
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
            'send_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Send Email'
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            'discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Amount'
        )->addColumn(
            'base_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Amount'
        )->addColumn(
            'shipping_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping Discount Tax Compensation Amount'
        )->addColumn(
            'base_shipping_discount_tax_compensation_amnt',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Shipping Discount Tax Compensation Amount'
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
        )->addColumn(
            'customer_note',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Customer Note'
        )->addColumn(
            'customer_note_notify',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Customer Note Notify'
        )->addIndex(
            $installer->getIdxName('sales_creditmemo', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo', ['order_id']),
            ['order_id']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo', ['creditmemo_status']),
            ['creditmemo_status']
        )->addIndex(
            $installer->getIdxName(
                'sales_creditmemo',
                ['increment_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_creditmemo', ['state']),
            ['state']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo', ['send_email']),
            ['send_email']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo', ['email_sent']),
            ['email_sent']
        )->addForeignKey(
            $installer->getFkName('sales_creditmemo', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('sales_creditmemo', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Flat Creditmemo'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_creditmemo_grid'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_creditmemo_grid')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
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
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Order Id'
        )->addColumn(
            'order_increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Order Increment Id'
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
        )->addColumn(
            'state',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Status'
        )->addColumn(
            'base_grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Grand Total'
        )->addColumn(
            'order_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Order Status'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'billing_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Billing Address'
        )->addColumn(
            'shipping_address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Address'
        )->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            ['nullable' => false],
            'Customer Name'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            [],
            'Customer Email'
        )->addColumn(
            'customer_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'Customer Group Id'
        )->addColumn(
            'payment_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Payment Method'
        )->addColumn(
            'shipping_information',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Shipping Method Name'
        )->addColumn(
            'subtotal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Subtotal'
        )->addColumn(
            'shipping_and_handling',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Shipping and handling amount'
        )->addColumn(
            'adjustment_positive',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Adjustment Positive'
        )->addColumn(
            'adjustment_negative',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Adjustment Negative'
        )->addColumn(
            'order_base_grand_total',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Order Grand Total'
        )->addIndex(
            $installer->getIdxName(
                'sales_creditmemo_grid',
                [
                    'increment_id',
                    'store_id'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['increment_id', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['order_increment_id']),
            ['order_increment_id']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['updated_at']),
            ['updated_at']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['order_created_at']),
            ['order_created_at']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['state']),
            ['state']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['billing_name']),
            ['billing_name']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['order_status']),
            ['order_status']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['base_grand_total']),
            ['base_grand_total']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['order_base_grand_total']),
            ['order_base_grand_total']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_grid', ['order_id']),
            ['order_id']
        )->addIndex(
            $installer->getIdxName(
                'sales_creditmemo_grid',
                [
                    'increment_id',
                    'order_increment_id',
                    'billing_name',
                    'billing_address',
                    'shipping_address',
                    'customer_name',
                    'customer_email'
                ],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            [
                'increment_id',
                'order_increment_id',
                'billing_name',
                'billing_address',
                'shipping_address',
                'customer_name',
                'customer_email'
            ],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Sales Flat Creditmemo Grid'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_creditmemo_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_creditmemo_item')
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
            'discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Discount Tax Compensation Amount'
        )->addColumn(
            'base_discount_tax_compensation_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            [],
            'Base Discount Tax Compensation Amount'
        )->addColumn(
            'tax_ratio',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            512,
            [],
            'Ratio of tax in the creditmemo item over tax of the order item'
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_item', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('sales_creditmemo_item', 'parent_id', 'sales_creditmemo', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_creditmemo'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Creditmemo Item'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_creditmemo_comment'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_creditmemo_comment')
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_comment', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('sales_creditmemo_comment', ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $installer->getFkName('sales_creditmemo_comment', 'parent_id', 'sales_creditmemo', 'entity_id'),
            'parent_id',
            $installer->getTable('sales_creditmemo'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Flat Creditmemo Comment'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_invoiced_aggregated'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_invoiced_aggregated')
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
            $installer->getIdxName(
                'sales_invoiced_aggregated',
                ['period', 'store_id', 'order_status'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'order_status'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_invoiced_aggregated', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('sales_invoiced_aggregated', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Invoiced Aggregated'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_invoiced_aggregated_order'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_invoiced_aggregated_order')
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
            $installer->getIdxName(
                'sales_invoiced_aggregated_order',
                ['period', 'store_id', 'order_status'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'order_status'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_invoiced_aggregated_order', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('sales_invoiced_aggregated_order', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Invoiced Aggregated Order'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_aggregated_created'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_aggregated_created')
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
            $installer->getIdxName(
                'sales_order_aggregated_created',
                ['period', 'store_id', 'order_status'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'order_status'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_order_aggregated_created', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('sales_order_aggregated_created', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Order Aggregated Created'
        );
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->createTable(
            $installer->getConnection()->createTableByDdl(
                $installer->getTable('sales_order_aggregated_created'),
                $installer->getTable('sales_order_aggregated_updated')
            )
        );

        /**
         * Create table 'sales_payment_transaction'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_payment_transaction')
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
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $installer->getIdxName(
                'sales_payment_transaction',
                ['order_id', 'payment_id', 'txn_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['order_id', 'payment_id', 'txn_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_payment_transaction', ['parent_id']),
            ['parent_id']
        )->addIndex(
            $installer->getIdxName('sales_payment_transaction', ['payment_id']),
            ['payment_id']
        )->addForeignKey(
            $installer->getFkName('sales_payment_transaction', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'sales_payment_transaction',
                'parent_id',
                'sales_payment_transaction',
                'transaction_id'
            ),
            'parent_id',
            $installer->getTable('sales_payment_transaction'),
            'transaction_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('sales_payment_transaction', 'payment_id', 'sales_order_payment', 'entity_id'),
            'payment_id',
            $installer->getTable('sales_order_payment'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Payment Transaction'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_refunded_aggregated'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_refunded_aggregated')
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
            $installer->getIdxName(
                'sales_refunded_aggregated',
                ['period', 'store_id', 'order_status'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'order_status'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_refunded_aggregated', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('sales_refunded_aggregated', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Refunded Aggregated'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_refunded_aggregated_order'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_refunded_aggregated_order')
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
            $installer->getIdxName(
                'sales_refunded_aggregated_order',
                ['period', 'store_id', 'order_status'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'order_status'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_refunded_aggregated_order', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('sales_refunded_aggregated_order', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Refunded Aggregated Order'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_shipping_aggregated'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_shipping_aggregated')
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
            $installer->getIdxName(
                'sales_shipping_aggregated',
                ['period', 'store_id', 'order_status', 'shipping_description'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'order_status', 'shipping_description'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_shipping_aggregated', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('sales_shipping_aggregated', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Shipping Aggregated'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_shipping_aggregated_order'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_shipping_aggregated_order')
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
            $installer->getIdxName(
                'sales_shipping_aggregated_order',
                ['period', 'store_id', 'order_status', 'shipping_description'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'order_status', 'shipping_description'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_shipping_aggregated_order', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('sales_shipping_aggregated_order', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Shipping Aggregated Order'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_bestsellers_aggregated_daily'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_bestsellers_aggregated_daily')
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
            $installer->getIdxName(
                'sales_bestsellers_aggregated_daily',
                ['period', 'store_id', 'product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'product_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_bestsellers_aggregated_daily', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('sales_bestsellers_aggregated_daily', ['product_id']),
            ['product_id']
        )->addForeignKey(
            $installer->getFkName('sales_bestsellers_aggregated_daily', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Bestsellers Aggregated Daily'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_bestsellers_aggregated_monthly'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_bestsellers_aggregated_monthly')
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
            $installer->getIdxName(
                'sales_bestsellers_aggregated_monthly',
                ['period', 'store_id', 'product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'product_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_bestsellers_aggregated_monthly', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('sales_bestsellers_aggregated_monthly', ['product_id']),
            ['product_id']
        )->addForeignKey(
            $installer->getFkName('sales_bestsellers_aggregated_monthly', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Bestsellers Aggregated Monthly'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_bestsellers_aggregated_yearly'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_bestsellers_aggregated_yearly')
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
            $installer->getIdxName(
                'sales_bestsellers_aggregated_yearly',
                ['period', 'store_id', 'product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['period', 'store_id', 'product_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('sales_bestsellers_aggregated_yearly', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('sales_bestsellers_aggregated_yearly', ['product_id']),
            ['product_id']
        )->addForeignKey(
            $installer->getFkName('sales_bestsellers_aggregated_yearly', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Bestsellers Aggregated Yearly'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_tax'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_tax')
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
        )->addIndex(
            $installer->getIdxName('sales_order_tax', ['order_id', 'priority', 'position']),
            ['order_id', 'priority', 'position']
        )->setComment(
            'Sales Order Tax Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_tax_item'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('sales_order_tax_item')
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
            $setup->getIdxName('sales_order_tax_item', ['item_id']),
            ['item_id']
        )->addIndex(
            $setup->getIdxName(
                'sales_order_tax_item',
                ['tax_id', 'item_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['tax_id', 'item_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $setup->getFkName('sales_order_tax_item', 'associated_item_id', 'sales_order_item', 'item_id'),
            'associated_item_id',
            $setup->getTable('sales_order_item'),
            'item_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('sales_order_tax_item', 'tax_id', 'sales_order_tax', 'tax_id'),
            'tax_id',
            $setup->getTable('sales_order_tax'),
            'tax_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('sales_order_tax_item', 'item_id', 'sales_order_item', 'item_id'),
            'item_id',
            $setup->getTable('sales_order_item'),
            'item_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Order Tax Item'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_status'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_status')
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
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_status_state'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_status_state')
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
            $installer->getFkName('sales_order_status_state', 'status', 'sales_order_status', 'status'),
            'status',
            $installer->getTable('sales_order_status'),
            'status',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Order Status Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'sales_order_status_label'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sales_order_status_label')
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
            $installer->getIdxName('sales_order_status_label', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('sales_order_status_label', 'status', 'sales_order_status', 'status'),
            'status',
            $installer->getTable('sales_order_status'),
            'status',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('sales_order_status_label', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Order Status Label Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
