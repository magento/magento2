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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'sales_flat_order'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_order'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'State')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Status')
    ->addColumn('coupon_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Coupon Code')
    ->addColumn('protect_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Protect Code')
    ->addColumn('shipping_description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Shipping Description')
    ->addColumn('is_virtual', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Virtual')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Id')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Discount Amount')
    ->addColumn('base_discount_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Discount Canceled')
    ->addColumn('base_discount_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Discount Invoiced')
    ->addColumn('base_discount_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Discount Refunded')
    ->addColumn('base_grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Grand Total')
    ->addColumn('base_shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Amount')
    ->addColumn('base_shipping_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Canceled')
    ->addColumn('base_shipping_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Invoiced')
    ->addColumn('base_shipping_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Refunded')
    ->addColumn('base_shipping_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Tax Amount')
    ->addColumn('base_shipping_tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Tax Refunded')
    ->addColumn('base_subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal')
    ->addColumn('base_subtotal_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal Canceled')
    ->addColumn('base_subtotal_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal Invoiced')
    ->addColumn('base_subtotal_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal Refunded')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Amount')
    ->addColumn('base_tax_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Canceled')
    ->addColumn('base_tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Invoiced')
    ->addColumn('base_tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Refunded')
    ->addColumn('base_to_global_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Global Rate')
    ->addColumn('base_to_order_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Order Rate')
    ->addColumn('base_total_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Canceled')
    ->addColumn('base_total_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Invoiced')
    ->addColumn('base_total_invoiced_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Invoiced Cost')
    ->addColumn('base_total_offline_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Offline Refunded')
    ->addColumn('base_total_online_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Online Refunded')
    ->addColumn('base_total_paid', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Paid')
    ->addColumn('base_total_qty_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Qty Ordered')
    ->addColumn('base_total_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Refunded')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Amount')
    ->addColumn('discount_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Canceled')
    ->addColumn('discount_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Invoiced')
    ->addColumn('discount_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Refunded')
    ->addColumn('grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Grand Total')
    ->addColumn('shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Amount')
    ->addColumn('shipping_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Canceled')
    ->addColumn('shipping_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Invoiced')
    ->addColumn('shipping_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Refunded')
    ->addColumn('shipping_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Tax Amount')
    ->addColumn('shipping_tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Tax Refunded')
    ->addColumn('store_to_base_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Store To Base Rate')
    ->addColumn('store_to_order_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Store To Order Rate')
    ->addColumn('subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal')
    ->addColumn('subtotal_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal Canceled')
    ->addColumn('subtotal_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal Invoiced')
    ->addColumn('subtotal_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal Refunded')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Amount')
    ->addColumn('tax_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Canceled')
    ->addColumn('tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Invoiced')
    ->addColumn('tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Refunded')
    ->addColumn('total_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Canceled')
    ->addColumn('total_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Invoiced')
    ->addColumn('total_offline_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Offline Refunded')
    ->addColumn('total_online_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Online Refunded')
    ->addColumn('total_paid', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Paid')
    ->addColumn('total_qty_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Qty Ordered')
    ->addColumn('total_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Refunded')
    ->addColumn('can_ship_partially', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Can Ship Partially')
    ->addColumn('can_ship_partially_item', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Can Ship Partially Item')
    ->addColumn('customer_is_guest', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Customer Is Guest')
    ->addColumn('customer_note_notify', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Customer Note Notify')
    ->addColumn('billing_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Billing Address Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        ), 'Customer Group Id')
    ->addColumn('edit_increment', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Edit Increment')
    ->addColumn('email_sent', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Email Sent')
    ->addColumn('forced_shipment_with_invoice', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Forced Do Shipment With Invoice')
    ->addColumn('payment_auth_expiration', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Payment Authorization Expiration')
    ->addColumn('quote_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Quote Address Id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Quote Id')
    ->addColumn('shipping_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Shipping Address Id')
    ->addColumn('adjustment_negative', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Adjustment Negative')
    ->addColumn('adjustment_positive', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Adjustment Positive')
    ->addColumn('base_adjustment_negative', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Adjustment Negative')
    ->addColumn('base_adjustment_positive', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Adjustment Positive')
    ->addColumn('base_shipping_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Discount Amount')
    ->addColumn('base_subtotal_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal Incl Tax')
    ->addColumn('base_total_due', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Due')
    ->addColumn('payment_authorization_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Payment Authorization Amount')
    ->addColumn('shipping_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Discount Amount')
    ->addColumn('subtotal_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal Incl Tax')
    ->addColumn('total_due', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Due')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Weight')
    ->addColumn('customer_dob', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        ), 'Customer Dob')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('applied_rule_ids', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Applied Rule Ids')
    ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Base Currency Code')
    ->addColumn('customer_email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Email')
    ->addColumn('customer_firstname', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Firstname')
    ->addColumn('customer_lastname', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Lastname')
    ->addColumn('customer_middlename', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Middlename')
    ->addColumn('customer_prefix', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Prefix')
    ->addColumn('customer_suffix', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Suffix')
    ->addColumn('customer_taxvat', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Taxvat')
    ->addColumn('discount_description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Discount Description')
    ->addColumn('ext_customer_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Ext Customer Id')
    ->addColumn('ext_order_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Ext Order Id')
    ->addColumn('global_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Global Currency Code')
    ->addColumn('hold_before_state', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Hold Before State')
    ->addColumn('hold_before_status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Hold Before Status')
    ->addColumn('order_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Order Currency Code')
    ->addColumn('original_increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Original Increment Id')
    ->addColumn('relation_child_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Relation Child Id')
    ->addColumn('relation_child_real_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Relation Child Real Id')
    ->addColumn('relation_parent_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Relation Parent Id')
    ->addColumn('relation_parent_real_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Relation Parent Real Id')
    ->addColumn('remote_ip', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Remote Ip')
    ->addColumn('shipping_method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Shipping Method')
    ->addColumn('store_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Store Currency Code')
    ->addColumn('store_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Store Name')
    ->addColumn('x_forwarded_for', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'X Forwarded For')
    ->addColumn('customer_note', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Customer Note')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addColumn('total_item_count', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Total Item Count')
    ->addColumn('customer_gender', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Customer Gender')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addColumn('shipping_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Hidden Tax Amount')
    ->addColumn('base_shipping_hidden_tax_amnt', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Hidden Tax Amount')
    ->addColumn('hidden_tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Invoiced')
    ->addColumn('base_hidden_tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Invoiced')
    ->addColumn('hidden_tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Refunded')
    ->addColumn('base_hidden_tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Refunded')
    ->addColumn('shipping_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Incl Tax')
    ->addColumn('base_shipping_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Incl Tax')
    ->addIndex($installer->getIdxName('sales_flat_order', array('status')),
        array('status'))
    ->addIndex($installer->getIdxName('sales_flat_order', array('state')),
        array('state'))
    ->addIndex($installer->getIdxName('sales_flat_order', array('store_id')),
        array('store_id'))
    ->addIndex(
        $installer->getIdxName(
            'sales_flat_order',
            array('increment_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('increment_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_flat_order', array('created_at')),
        array('created_at'))
    ->addIndex($installer->getIdxName('sales_flat_order', array('customer_id')),
        array('customer_id'))
    ->addIndex($installer->getIdxName('sales_flat_order', array('ext_order_id')),
        array('ext_order_id'))
    ->addIndex($installer->getIdxName('sales_flat_order', array('quote_id')),
        array('quote_id'))
    ->addIndex($installer->getIdxName('sales_flat_order', array('updated_at')),
        array('updated_at'))
    ->addForeignKey($installer->getFkName('sales_flat_order', 'customer_id', 'customer_entity', 'entity_id'),
        'customer_id', $installer->getTable('customer_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_order', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Order');
$installer->getConnection()->createTable($table);

/**
 * Create table 'sales_flat_order_grid'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_order_grid'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Status')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('store_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Store Name')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Id')
    ->addColumn('base_grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Grand Total')
    ->addColumn('base_total_paid', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Paid')
    ->addColumn('grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Grand Total')
    ->addColumn('total_paid', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Paid')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Base Currency Code')
    ->addColumn('order_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Order Currency Code')
    ->addColumn('shipping_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Shipping Name')
    ->addColumn('billing_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Billing Name')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('status')),
        array('status'))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('base_grand_total')),
        array('base_grand_total'))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('base_total_paid')),
        array('base_total_paid'))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('grand_total')),
        array('grand_total'))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('total_paid')),
        array('total_paid'))
    ->addIndex(
        $installer->getIdxName(
            'sales_flat_order_grid',
            array('increment_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('increment_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('shipping_name')),
        array('shipping_name'))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('billing_name')),
        array('billing_name'))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('created_at')),
        array('created_at'))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('customer_id')),
        array('customer_id'))
    ->addIndex($installer->getIdxName('sales_flat_order_grid', array('updated_at')),
        array('updated_at'))
    ->addForeignKey($installer->getFkName('sales_flat_order_grid', 'customer_id', 'customer_entity', 'entity_id'),
        'customer_id', $installer->getTable('customer_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_order_grid', 'entity_id', 'sales_flat_order', 'entity_id'),
        'entity_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_order_grid', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Order Grid');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_order_address'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_order_address'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent Id')
    ->addColumn('customer_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Customer Address Id')
    ->addColumn('quote_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Quote Address Id')
    ->addColumn('region_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Region Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Customer Id')
    ->addColumn('fax', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Fax')
    ->addColumn('region', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Region')
    ->addColumn('postcode', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Postcode')
    ->addColumn('lastname', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Lastname')
    ->addColumn('street', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Street')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'City')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Email')
    ->addColumn('telephone', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Telephone')
    ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
        ), 'Country Id')
    ->addColumn('firstname', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Firstname')
    ->addColumn('address_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Address Type')
    ->addColumn('prefix', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Prefix')
    ->addColumn('middlename', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Middlename')
    ->addColumn('suffix', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Suffix')
    ->addColumn('company', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Company')
    ->addIndex($installer->getIdxName('sales_flat_order_address', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('sales_flat_order_address', 'parent_id', 'sales_flat_order', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Order Address');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_order_status_history'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_order_status_history'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('is_customer_notified', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Is Customer Notified')
    ->addColumn('is_visible_on_front', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Visible On Front')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Comment')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Status')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addIndex($installer->getIdxName('sales_flat_order_status_history', array('parent_id')),
        array('parent_id'))
    ->addIndex($installer->getIdxName('sales_flat_order_status_history', array('created_at')),
        array('created_at'))
    ->addForeignKey($installer->getFkName('sales_flat_order_status_history', 'parent_id', 'sales_flat_order', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Order Status History');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_order_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_order_item'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Item Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Order Id')
    ->addColumn('parent_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent Item Id')
    ->addColumn('quote_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Quote Item Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Product Id')
    ->addColumn('product_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Product Type')
    ->addColumn('product_options', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Product Options')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Weight')
    ->addColumn('is_virtual', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Virtual')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('applied_rule_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Applied Rule Ids')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('free_shipping', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Free Shipping')
    ->addColumn('is_qty_decimal', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Qty Decimal')
    ->addColumn('no_discount', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'No Discount')
    ->addColumn('qty_backordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Backordered')
    ->addColumn('qty_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Canceled')
    ->addColumn('qty_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Invoiced')
    ->addColumn('qty_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Ordered')
    ->addColumn('qty_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Refunded')
    ->addColumn('qty_shipped', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Qty Shipped')
    ->addColumn('base_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Cost')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Price')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Price')
    ->addColumn('original_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Original Price')
    ->addColumn('base_original_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Original Price')
    ->addColumn('tax_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Percent')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Amount')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Tax Amount')
    ->addColumn('tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Invoiced')
    ->addColumn('base_tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Tax Invoiced')
    ->addColumn('discount_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Percent')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Amount')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Discount Amount')
    ->addColumn('discount_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Invoiced')
    ->addColumn('base_discount_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Discount Invoiced')
    ->addColumn('amount_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Amount Refunded')
    ->addColumn('base_amount_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Amount Refunded')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Row Total')
    ->addColumn('base_row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Row Total')
    ->addColumn('row_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Row Invoiced')
    ->addColumn('base_row_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Row Invoiced')
    ->addColumn('row_weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Row Weight')
    ->addColumn('base_tax_before_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Before Discount')
    ->addColumn('tax_before_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Before Discount')
    ->addColumn('ext_order_item_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Ext Order Item Id')
    ->addColumn('locked_do_invoice', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Locked Do Invoice')
    ->addColumn('locked_do_ship', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Locked Do Ship')
    ->addColumn('price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price Incl Tax')
    ->addColumn('base_price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price Incl Tax')
    ->addColumn('row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total Incl Tax')
    ->addColumn('base_row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total Incl Tax')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addColumn('hidden_tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Invoiced')
    ->addColumn('base_hidden_tax_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Invoiced')
    ->addColumn('hidden_tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Refunded')
    ->addColumn('base_hidden_tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Refunded')
    ->addColumn('is_nominal', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Nominal')
    ->addColumn('tax_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Canceled')
    ->addColumn('hidden_tax_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Canceled')
    ->addColumn('tax_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Refunded')
    ->addIndex($installer->getIdxName('sales_flat_order_item', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('sales_flat_order_item', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_flat_order_item', 'order_id', 'sales_flat_order', 'entity_id'),
        'order_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_order_item', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Order Item');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_order_payment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_order_payment'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('base_shipping_captured', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Captured')
    ->addColumn('shipping_captured', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Captured')
    ->addColumn('amount_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Amount Refunded')
    ->addColumn('base_amount_paid', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Amount Paid')
    ->addColumn('amount_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Amount Canceled')
    ->addColumn('base_amount_authorized', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Amount Authorized')
    ->addColumn('base_amount_paid_online', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Amount Paid Online')
    ->addColumn('base_amount_refunded_online', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Amount Refunded Online')
    ->addColumn('base_shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Amount')
    ->addColumn('shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Amount')
    ->addColumn('amount_paid', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Amount Paid')
    ->addColumn('amount_authorized', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Amount Authorized')
    ->addColumn('base_amount_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Amount Ordered')
    ->addColumn('base_shipping_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Refunded')
    ->addColumn('shipping_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Refunded')
    ->addColumn('base_amount_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Amount Refunded')
    ->addColumn('amount_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Amount Ordered')
    ->addColumn('base_amount_canceled', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Amount Canceled')
    ->addColumn('quote_payment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Quote Payment Id')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('cc_exp_month', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Exp Month')
    ->addColumn('cc_ss_start_year', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Ss Start Year')
    ->addColumn('echeck_bank_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Echeck Bank Name')
    ->addColumn('method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Method')
    ->addColumn('cc_debug_request_body', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Debug Request Body')
    ->addColumn('cc_secure_verify', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Secure Verify')
    ->addColumn('protection_eligibility', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Protection Eligibility')
    ->addColumn('cc_approval', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Approval')
    ->addColumn('cc_last4', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Last4')
    ->addColumn('cc_status_description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Status Description')
    ->addColumn('echeck_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Echeck Type')
    ->addColumn('cc_debug_response_serialized', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Debug Response Serialized')
    ->addColumn('cc_ss_start_month', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Ss Start Month')
    ->addColumn('echeck_account_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Echeck Account Type')
    ->addColumn('last_trans_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Last Trans Id')
    ->addColumn('cc_cid_status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Cid Status')
    ->addColumn('cc_owner', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Owner')
    ->addColumn('cc_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Type')
    ->addColumn('po_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Po Number')
    ->addColumn('cc_exp_year', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Exp Year')
    ->addColumn('cc_status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Status')
    ->addColumn('echeck_routing_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Echeck Routing Number')
    ->addColumn('account_status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Account Status')
    ->addColumn('anet_trans_method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Anet Trans Method')
    ->addColumn('cc_debug_response_body', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Debug Response Body')
    ->addColumn('cc_ss_issue', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Ss Issue')
    ->addColumn('echeck_account_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Echeck Account Name')
    ->addColumn('cc_avs_status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Avs Status')
    ->addColumn('cc_number_enc', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Number Enc')
    ->addColumn('cc_trans_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Trans Id')
    ->addColumn('address_status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Address Status')
    ->addColumn('additional_information', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Information')
    ->addIndex($installer->getIdxName('sales_flat_order_payment', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('sales_flat_order_payment', 'parent_id', 'sales_flat_order', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Order Payment');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_shipment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_shipment'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('total_weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Weight')
    ->addColumn('total_qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Qty')
    ->addColumn('email_sent', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Email Sent')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Customer Id')
    ->addColumn('shipping_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Shipping Address Id')
    ->addColumn('billing_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Billing Address Id')
    ->addColumn('shipment_status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Shipment Status')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addIndex($installer->getIdxName('sales_flat_shipment', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_flat_shipment', array('total_qty')),
        array('total_qty'))
    ->addIndex(
        $installer->getIdxName(
            'sales_flat_shipment',
            array('increment_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('increment_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_flat_shipment', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('sales_flat_shipment', array('created_at')),
        array('created_at'))
    ->addIndex($installer->getIdxName('sales_flat_shipment', array('updated_at')),
        array('updated_at'))
    ->addForeignKey($installer->getFkName('sales_flat_shipment', 'order_id', 'sales_flat_order', 'entity_id'),
        'order_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_shipment', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Shipment');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_shipment_grid'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_shipment_grid'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('total_qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Qty')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('shipment_status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Shipment Status')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Increment Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('order_created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Order Created At')
    ->addColumn('shipping_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Shipping Name')
    ->addIndex($installer->getIdxName('sales_flat_shipment_grid', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_flat_shipment_grid', array('total_qty')),
        array('total_qty'))
    ->addIndex($installer->getIdxName('sales_flat_shipment_grid', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('sales_flat_shipment_grid', array('shipment_status')),
        array('shipment_status'))
    ->addIndex(
        $installer->getIdxName(
            'sales_flat_shipment_grid',
            array('increment_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('increment_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_flat_shipment_grid', array('order_increment_id')),
        array('order_increment_id'))
    ->addIndex($installer->getIdxName('sales_flat_shipment_grid', array('created_at')),
        array('created_at'))
    ->addIndex($installer->getIdxName('sales_flat_shipment_grid', array('order_created_at')),
        array('order_created_at'))
    ->addIndex($installer->getIdxName('sales_flat_shipment_grid', array('shipping_name')),
        array('shipping_name'))
    ->addForeignKey($installer->getFkName('sales_flat_shipment_grid', 'entity_id', 'sales_flat_shipment', 'entity_id'),
        'entity_id', $installer->getTable('sales_flat_shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_shipment_grid', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Shipment Grid');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_shipment_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_shipment_item'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Weight')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Product Id')
    ->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Order Item Id')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addIndex($installer->getIdxName('sales_flat_shipment_item', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('sales_flat_shipment_item', 'parent_id', 'sales_flat_shipment', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Shipment Item');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_shipment_track'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_shipment_track'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Weight')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('track_number', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Number')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Title')
    ->addColumn('carrier_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Carrier Code')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addIndex($installer->getIdxName('sales_flat_shipment_track', array('parent_id')),
        array('parent_id'))
    ->addIndex($installer->getIdxName('sales_flat_shipment_track', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('sales_flat_shipment_track', array('created_at')),
        array('created_at'))
    ->addForeignKey($installer->getFkName('sales_flat_shipment_track', 'parent_id', 'sales_flat_shipment', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Shipment Track');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_shipment_comment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_shipment_comment'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('is_customer_notified', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Is Customer Notified')
    ->addColumn('is_visible_on_front', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Visible On Front')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Comment')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addIndex($installer->getIdxName('sales_flat_shipment_comment', array('created_at')),
        array('created_at'))
    ->addIndex($installer->getIdxName('sales_flat_shipment_comment', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('sales_flat_shipment_comment', 'parent_id', 'sales_flat_shipment', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Shipment Comment');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_invoice'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_invoice'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'identity'  => true
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('base_grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Grand Total')
    ->addColumn('shipping_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Tax Amount')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Amount')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Amount')
    ->addColumn('store_to_order_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Store To Order Rate')
    ->addColumn('base_shipping_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Tax Amount')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Discount Amount')
    ->addColumn('base_to_order_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Order Rate')
    ->addColumn('grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Grand Total')
    ->addColumn('shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Amount')
    ->addColumn('subtotal_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal Incl Tax')
    ->addColumn('base_subtotal_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal Incl Tax')
    ->addColumn('store_to_base_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Store To Base Rate')
    ->addColumn('base_shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Amount')
    ->addColumn('total_qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Qty')
    ->addColumn('base_to_global_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Global Rate')
    ->addColumn('subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal')
    ->addColumn('base_subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Amount')
    ->addColumn('billing_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Billing Address Id')
    ->addColumn('is_used_for_refund', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Used For Refund')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('email_sent', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Email Sent')
    ->addColumn('can_void_flag', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Can Void Flag')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'State')
    ->addColumn('shipping_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Shipping Address Id')
    ->addColumn('store_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Store Currency Code')
    ->addColumn('transaction_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Transaction Id')
    ->addColumn('order_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Order Currency Code')
    ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Base Currency Code')
    ->addColumn('global_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Global Currency Code')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addColumn('shipping_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Hidden Tax Amount')
    ->addColumn('base_shipping_hidden_tax_amnt', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Hidden Tax Amount')
    ->addColumn('shipping_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Incl Tax')
    ->addColumn('base_shipping_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Incl Tax')
    ->addColumn('base_total_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Total Refunded')
    ->addIndex($installer->getIdxName('sales_flat_invoice', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_flat_invoice', array('grand_total')),
        array('grand_total'))
    ->addIndex($installer->getIdxName('sales_flat_invoice', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('sales_flat_invoice', array('state')),
        array('state'))
    ->addIndex(
        $installer->getIdxName(
            'sales_flat_invoice',
            array('increment_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('increment_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_flat_invoice', array('created_at')),
        array('created_at'))
    ->addForeignKey($installer->getFkName('sales_flat_invoice', 'order_id', 'sales_flat_order', 'entity_id'),
        'order_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_invoice', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Invoice');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_invoice_grid'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_invoice_grid'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('base_grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Grand Total')
    ->addColumn('grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Grand Total')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'State')
    ->addColumn('store_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Store Currency Code')
    ->addColumn('order_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Order Currency Code')
    ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Base Currency Code')
    ->addColumn('global_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Global Currency Code')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Increment Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('order_created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Order Created At')
    ->addColumn('billing_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Billing Name')
    ->addIndex($installer->getIdxName('sales_flat_invoice_grid', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_flat_invoice_grid', array('grand_total')),
        array('grand_total'))
    ->addIndex($installer->getIdxName('sales_flat_invoice_grid', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('sales_flat_invoice_grid', array('state')),
        array('state'))
    ->addIndex(
        $installer->getIdxName(
            'sales_flat_invoice_grid',
            array('increment_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('increment_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_flat_invoice_grid', array('order_increment_id')),
        array('order_increment_id'))
    ->addIndex($installer->getIdxName('sales_flat_invoice_grid', array('created_at')),
        array('created_at'))
    ->addIndex($installer->getIdxName('sales_flat_invoice_grid', array('order_created_at')),
        array('order_created_at'))
    ->addIndex($installer->getIdxName('sales_flat_invoice_grid', array('billing_name')),
        array('billing_name'))
    ->addForeignKey($installer->getFkName('sales_flat_invoice_grid', 'entity_id', 'sales_flat_invoice', 'entity_id'),
        'entity_id', $installer->getTable('sales_flat_invoice'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_invoice_grid', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Invoice Grid');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_invoice_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_invoice_item'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Amount')
    ->addColumn('base_row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Amount')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Discount Amount')
    ->addColumn('price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price Incl Tax')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Amount')
    ->addColumn('base_price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price Incl Tax')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty')
    ->addColumn('base_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Cost')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('base_row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total Incl Tax')
    ->addColumn('row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total Incl Tax')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Product Id')
    ->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Order Item Id')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addIndex($installer->getIdxName('sales_flat_invoice_item', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('sales_flat_invoice_item', 'parent_id', 'sales_flat_invoice', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_invoice'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Invoice Item');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_invoice_comment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_invoice_comment'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('is_customer_notified', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Customer Notified')
    ->addColumn('is_visible_on_front', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Visible On Front')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Comment')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addIndex($installer->getIdxName('sales_flat_invoice_comment', array('created_at')),
        array('created_at'))
    ->addIndex($installer->getIdxName('sales_flat_invoice_comment', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('sales_flat_invoice_comment', 'parent_id', 'sales_flat_invoice', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_invoice'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Invoice Comment');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_creditmemo'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_creditmemo'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('adjustment_positive', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Adjustment Positive')
    ->addColumn('base_shipping_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Tax Amount')
    ->addColumn('store_to_order_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Store To Order Rate')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Discount Amount')
    ->addColumn('base_to_order_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Order Rate')
    ->addColumn('grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Grand Total')
    ->addColumn('base_adjustment_negative', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Adjustment Negative')
    ->addColumn('base_subtotal_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal Incl Tax')
    ->addColumn('shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Amount')
    ->addColumn('subtotal_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal Incl Tax')
    ->addColumn('adjustment_negative', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Adjustment Negative')
    ->addColumn('base_shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Amount')
    ->addColumn('store_to_base_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Store To Base Rate')
    ->addColumn('base_to_global_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Global Rate')
    ->addColumn('base_adjustment', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Adjustment')
    ->addColumn('base_subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Amount')
    ->addColumn('subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal')
    ->addColumn('adjustment', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Adjustment')
    ->addColumn('base_grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Grand Total')
    ->addColumn('base_adjustment_positive', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Adjustment Positive')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Amount')
    ->addColumn('shipping_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Tax Amount')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Amount')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('email_sent', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Email Sent')
    ->addColumn('creditmemo_status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Creditmemo Status')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'State')
    ->addColumn('shipping_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Shipping Address Id')
    ->addColumn('billing_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Billing Address Id')
    ->addColumn('invoice_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Invoice Id')
    ->addColumn('store_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Store Currency Code')
    ->addColumn('order_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Order Currency Code')
    ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Base Currency Code')
    ->addColumn('global_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Global Currency Code')
    ->addColumn('transaction_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Transaction Id')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addColumn('shipping_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Hidden Tax Amount')
    ->addColumn('base_shipping_hidden_tax_amnt', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Hidden Tax Amount')
    ->addColumn('shipping_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Incl Tax')
    ->addColumn('base_shipping_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Incl Tax')
    ->addIndex($installer->getIdxName('sales_flat_creditmemo', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo', array('creditmemo_status')),
        array('creditmemo_status'))
    ->addIndex(
        $installer->getIdxName(
            'sales_flat_creditmemo',
            array('increment_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('increment_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo', array('state')),
        array('state'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo', array('created_at')),
        array('created_at'))
    ->addForeignKey($installer->getFkName('sales_flat_creditmemo', 'order_id', 'sales_flat_order', 'entity_id'),
        'order_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_creditmemo', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Creditmemo');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_creditmemo_grid'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_creditmemo_grid'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('store_to_order_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Store To Order Rate')
    ->addColumn('base_to_order_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Order Rate')
    ->addColumn('grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Grand Total')
    ->addColumn('store_to_base_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Store To Base Rate')
    ->addColumn('base_to_global_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Global Rate')
    ->addColumn('base_grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Grand Total')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('creditmemo_status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Creditmemo Status')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'State')
    ->addColumn('invoice_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Invoice Id')
    ->addColumn('store_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Store Currency Code')
    ->addColumn('order_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Order Currency Code')
    ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Base Currency Code')
    ->addColumn('global_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        ), 'Global Currency Code')
    ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Increment Id')
    ->addColumn('order_increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Increment Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addColumn('order_created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Order Created At')
    ->addColumn('billing_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Billing Name')
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('grand_total')),
        array('grand_total'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('base_grand_total')),
        array('base_grand_total'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('creditmemo_status')),
        array('creditmemo_status'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('state')),
        array('state'))
    ->addIndex(
        $installer->getIdxName(
            'sales_flat_creditmemo_grid',
            array('increment_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('increment_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('order_increment_id')),
        array('order_increment_id'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('created_at')),
        array('created_at'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('order_created_at')),
        array('order_created_at'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_grid', array('billing_name')),
        array('billing_name'))
    ->addForeignKey($installer->getFkName('sales_flat_creditmemo_grid', 'entity_id', 'sales_flat_creditmemo', 'entity_id'),
        'entity_id', $installer->getTable('sales_flat_creditmemo'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_creditmemo_grid', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Creditmemo Grid');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_creditmemo_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_creditmemo_item'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Amount')
    ->addColumn('base_row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Amount')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Discount Amount')
    ->addColumn('price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price Incl Tax')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Amount')
    ->addColumn('base_price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price Incl Tax')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Qty')
    ->addColumn('base_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Cost')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('base_row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total Incl Tax')
    ->addColumn('row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total Incl Tax')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Product Id')
    ->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Order Item Id')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_item', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('sales_flat_creditmemo_item', 'parent_id', 'sales_flat_creditmemo', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_creditmemo'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Creditmemo Item');
$installer->getConnection()->createTable($table);

/**
 * Create table 'sales_flat_creditmemo_comment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_creditmemo_comment'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Parent Id')
    ->addColumn('is_customer_notified', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Is Customer Notified')
    ->addColumn('is_visible_on_front', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Visible On Front')
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Comment')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_comment', array('created_at')),
        array('created_at'))
    ->addIndex($installer->getIdxName('sales_flat_creditmemo_comment', array('parent_id')),
        array('parent_id'))
    ->addForeignKey($installer->getFkName('sales_flat_creditmemo_comment', 'parent_id', 'sales_flat_creditmemo', 'entity_id'),
        'parent_id', $installer->getTable('sales_flat_creditmemo'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Creditmemo Comment');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_quote'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_quote'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Store Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('converted_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Converted At')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '1',
        ), 'Is Active')
    ->addColumn('is_virtual', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Is Virtual')
    ->addColumn('is_multi_shipping', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Is Multi Shipping')
    ->addColumn('items_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Items Count')
    ->addColumn('items_qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Items Qty')
    ->addColumn('orig_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Orig Order Id')
    ->addColumn('store_to_base_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Store To Base Rate')
    ->addColumn('store_to_quote_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Store To Quote Rate')
    ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Base Currency Code')
    ->addColumn('store_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Store Currency Code')
    ->addColumn('quote_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Quote Currency Code')
    ->addColumn('grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Grand Total')
    ->addColumn('base_grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Grand Total')
    ->addColumn('checkout_method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Checkout Method')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Customer Id')
    ->addColumn('customer_tax_class_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Customer Tax Class Id')
    ->addColumn('customer_group_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Customer Group Id')
    ->addColumn('customer_email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Email')
    ->addColumn('customer_prefix', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'Customer Prefix')
    ->addColumn('customer_firstname', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Firstname')
    ->addColumn('customer_middlename', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'Customer Middlename')
    ->addColumn('customer_lastname', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Lastname')
    ->addColumn('customer_suffix', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'Customer Suffix')
    ->addColumn('customer_dob', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        ), 'Customer Dob')
    ->addColumn('customer_note', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Note')
    ->addColumn('customer_note_notify', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '1',
        ), 'Customer Note Notify')
    ->addColumn('customer_is_guest', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Customer Is Guest')
    ->addColumn('remote_ip', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Remote Ip')
    ->addColumn('applied_rule_ids', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Applied Rule Ids')
    ->addColumn('reserved_order_id', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
        'nullable'  => true,
        ), 'Reserved Order Id')
    ->addColumn('password_hash', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Password Hash')
    ->addColumn('coupon_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Coupon Code')
    ->addColumn('global_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Global Currency Code')
    ->addColumn('base_to_global_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Global Rate')
    ->addColumn('base_to_quote_rate', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base To Quote Rate')
    ->addColumn('customer_taxvat', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Taxvat')
    ->addColumn('customer_gender', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Customer Gender')
    ->addColumn('subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal')
    ->addColumn('base_subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal')
    ->addColumn('subtotal_with_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal With Discount')
    ->addColumn('base_subtotal_with_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal With Discount')
    ->addColumn('is_changed', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Is Changed')
    ->addColumn('trigger_recollect', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Trigger Recollect')
    ->addColumn('ext_shipping_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Ext Shipping Info')
    ->addIndex($installer->getIdxName('sales_flat_quote', array('customer_id', 'store_id', 'is_active')),
        array('customer_id', 'store_id', 'is_active'))
    ->addIndex($installer->getIdxName('sales_flat_quote', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_flat_quote', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Quote');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_quote_address'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_quote_address'))
    ->addColumn('address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Address Id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Quote Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Id')
    ->addColumn('save_in_address_book', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'default'   => '0',
        ), 'Save In Address Book')
    ->addColumn('customer_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Address Id')
    ->addColumn('address_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Address Type')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Email')
    ->addColumn('prefix', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'Prefix')
    ->addColumn('firstname', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Firstname')
    ->addColumn('middlename', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'Middlename')
    ->addColumn('lastname', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Lastname')
    ->addColumn('suffix', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(
        ), 'Suffix')
    ->addColumn('company', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Company')
    ->addColumn('street', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Street')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'City')
    ->addColumn('region', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Region')
    ->addColumn('region_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Region Id')
    ->addColumn('postcode', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Postcode')
    ->addColumn('country_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Country Id')
    ->addColumn('telephone', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Telephone')
    ->addColumn('fax', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Fax')
    ->addColumn('same_as_billing', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Same As Billing')
    ->addColumn('free_shipping', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Free Shipping')
    ->addColumn('collect_shipping_rates', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Collect Shipping Rates')
    ->addColumn('shipping_method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Shipping Method')
    ->addColumn('shipping_description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Shipping Description')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Weight')
    ->addColumn('subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Subtotal')
    ->addColumn('base_subtotal', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Subtotal')
    ->addColumn('subtotal_with_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Subtotal With Discount')
    ->addColumn('base_subtotal_with_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Subtotal With Discount')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Tax Amount')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Tax Amount')
    ->addColumn('shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Shipping Amount')
    ->addColumn('base_shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Shipping Amount')
    ->addColumn('shipping_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Tax Amount')
    ->addColumn('base_shipping_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Tax Amount')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Discount Amount')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Discount Amount')
    ->addColumn('grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Grand Total')
    ->addColumn('base_grand_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Grand Total')
    ->addColumn('customer_notes', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Customer Notes')
    ->addColumn('applied_taxes', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Applied Taxes')
    ->addColumn('discount_description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Discount Description')
    ->addColumn('shipping_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Discount Amount')
    ->addColumn('base_shipping_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Discount Amount')
    ->addColumn('subtotal_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Subtotal Incl Tax')
    ->addColumn('base_subtotal_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Subtotal Total Incl Tax')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addColumn('shipping_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Hidden Tax Amount')
    ->addColumn('base_shipping_hidden_tax_amnt', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Hidden Tax Amount')
    ->addColumn('shipping_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Incl Tax')
    ->addColumn('base_shipping_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Shipping Incl Tax')
    ->addIndex($installer->getIdxName('sales_flat_quote_address', array('quote_id')),
        array('quote_id'))
    ->addForeignKey($installer->getFkName('sales_flat_quote_address', 'quote_id', 'sales_flat_quote', 'entity_id'),
        'quote_id', $installer->getTable('sales_flat_quote'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Quote Address');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_quote_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_quote_item'))
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Item Id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Quote Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Product Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('parent_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent Item Id')
    ->addColumn('is_virtual', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Virtual')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('applied_rule_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Applied Rule Ids')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('free_shipping', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Free Shipping')
    ->addColumn('is_qty_decimal', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Is Qty Decimal')
    ->addColumn('no_discount', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'No Discount')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Weight')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Qty')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Price')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Price')
    ->addColumn('custom_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Custom Price')
    ->addColumn('discount_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Percent')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Amount')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Discount Amount')
    ->addColumn('tax_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Percent')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Amount')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Tax Amount')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Row Total')
    ->addColumn('base_row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Row Total')
    ->addColumn('row_total_with_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Row Total With Discount')
    ->addColumn('row_weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Row Weight')
    ->addColumn('product_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Product Type')
    ->addColumn('base_tax_before_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Tax Before Discount')
    ->addColumn('tax_before_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Before Discount')
    ->addColumn('original_custom_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Original Custom Price')
    ->addColumn('redirect_url', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Redirect Url')
    ->addColumn('base_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Cost')
    ->addColumn('price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price Incl Tax')
    ->addColumn('base_price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price Incl Tax')
    ->addColumn('row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total Incl Tax')
    ->addColumn('base_row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total Incl Tax')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addIndex($installer->getIdxName('sales_flat_quote_item', array('parent_item_id')),
        array('parent_item_id'))
    ->addIndex($installer->getIdxName('sales_flat_quote_item', array('product_id')),
        array('product_id'))
    ->addIndex($installer->getIdxName('sales_flat_quote_item', array('quote_id')),
        array('quote_id'))
    ->addIndex($installer->getIdxName('sales_flat_quote_item', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_flat_quote_item', 'parent_item_id', 'sales_flat_quote_item', 'item_id'),
        'parent_item_id', $installer->getTable('sales_flat_quote_item'), 'item_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_quote_item', 'product_id', 'catalog_product_entity', 'entity_id'),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_quote_item', 'quote_id', 'sales_flat_quote', 'entity_id'),
        'quote_id', $installer->getTable('sales_flat_quote'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_flat_quote_item', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Quote Item');
$installer->getConnection()->createTable($table);

/**
 * Create table 'sales_flat_quote_address_item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_quote_address_item'))
    ->addColumn('address_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Address Item Id')
    ->addColumn('parent_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent Item Id')
    ->addColumn('quote_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Quote Address Id')
    ->addColumn('quote_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Quote Item Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('applied_rule_ids', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Applied Rule Ids')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Weight')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Qty')
    ->addColumn('discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Discount Amount')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Tax Amount')
    ->addColumn('row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Row Total')
    ->addColumn('base_row_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Base Row Total')
    ->addColumn('row_total_with_discount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Row Total With Discount')
    ->addColumn('base_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Discount Amount')
    ->addColumn('base_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Base Tax Amount')
    ->addColumn('row_weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'default'   => '0.0000',
        ), 'Row Weight')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Product Id')
    ->addColumn('super_product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Super Product Id')
    ->addColumn('parent_product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent Product Id')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Sku')
    ->addColumn('image', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Image')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Name')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Description')
    ->addColumn('free_shipping', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Free Shipping')
    ->addColumn('is_qty_decimal', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Is Qty Decimal')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price')
    ->addColumn('discount_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Discount Percent')
    ->addColumn('no_discount', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'No Discount')
    ->addColumn('tax_percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Percent')
    ->addColumn('base_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price')
    ->addColumn('base_cost', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Cost')
    ->addColumn('price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Price Incl Tax')
    ->addColumn('base_price_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Price Incl Tax')
    ->addColumn('row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Row Total Incl Tax')
    ->addColumn('base_row_total_incl_tax', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Row Total Incl Tax')
    ->addColumn('hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Hidden Tax Amount')
    ->addColumn('base_hidden_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Hidden Tax Amount')
    ->addIndex($installer->getIdxName('sales_flat_quote_address_item', array('quote_address_id')),
        array('quote_address_id'))
    ->addIndex($installer->getIdxName('sales_flat_quote_address_item', array('parent_item_id')),
        array('parent_item_id'))
    ->addIndex($installer->getIdxName('sales_flat_quote_address_item', array('quote_item_id')),
        array('quote_item_id'))
    ->addForeignKey(
        $installer->getFkName(
            'sales_flat_quote_address_item',
            'quote_address_id',
            'sales_flat_quote_address',
            'address_id'
        ),
        'quote_address_id', $installer->getTable('sales_flat_quote_address'), 'address_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'sales_flat_quote_address_item',
            'parent_item_id',
            'sales_flat_quote_address_item',
            'address_item_id'
        ),
        'parent_item_id', $installer->getTable('sales_flat_quote_address_item'), 'address_item_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'sales_flat_quote_address_item',
            'quote_item_id',
            'sales_flat_quote_item',
            'item_id'
        ),
        'quote_item_id', $installer->getTable('sales_flat_quote_item'), 'item_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Quote Address Item');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_quote_item_option'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_quote_item_option'))
    ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Option Id')
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Item Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Product Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Code')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Value')
    ->addIndex($installer->getIdxName('sales_flat_quote_item_option', array('item_id')),
        array('item_id'))
    ->addForeignKey($installer->getFkName('sales_flat_quote_item_option', 'item_id', 'sales_flat_quote_item', 'item_id'),
        'item_id', $installer->getTable('sales_flat_quote_item'), 'item_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Quote Item Option');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_quote_payment'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_quote_payment'))
    ->addColumn('payment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Payment Id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Quote Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Method')
    ->addColumn('cc_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Type')
    ->addColumn('cc_number_enc', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Number Enc')
    ->addColumn('cc_last4', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Last4')
    ->addColumn('cc_cid_enc', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Cid Enc')
    ->addColumn('cc_owner', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Owner')
    ->addColumn('cc_exp_month', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Cc Exp Month')
    ->addColumn('cc_exp_year', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Cc Exp Year')
    ->addColumn('cc_ss_owner', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Ss Owner')
    ->addColumn('cc_ss_start_month', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Cc Ss Start Month')
    ->addColumn('cc_ss_start_year', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'default'   => '0',
        ), 'Cc Ss Start Year')
    ->addColumn('po_number', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Po Number')
    ->addColumn('additional_data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Data')
    ->addColumn('cc_ss_issue', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Cc Ss Issue')
    ->addColumn('additional_information', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Information')
    ->addIndex($installer->getIdxName('sales_flat_quote_payment', array('quote_id')),
        array('quote_id'))
    ->addForeignKey($installer->getFkName('sales_flat_quote_payment', 'quote_id', 'sales_flat_quote', 'entity_id'),
        'quote_id', $installer->getTable('sales_flat_quote'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Quote Payment');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_flat_quote_shipping_rate'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_flat_quote_shipping_rate'))
    ->addColumn('rate_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Rate Id')
    ->addColumn('address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Address Id')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Updated At')
    ->addColumn('carrier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Carrier')
    ->addColumn('carrier_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Carrier Title')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Code')
    ->addColumn('method', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Method')
    ->addColumn('method_description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Method Description')
    ->addColumn('price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Price')
    ->addColumn('error_message', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Error Message')
    ->addColumn('method_title', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Method Title')
    ->addIndex($installer->getIdxName('sales_flat_quote_shipping_rate', array('address_id')),
        array('address_id'))
    ->addForeignKey(
        $installer->getFkName(
            'sales_flat_quote_shipping_rate',
            'address_id',
            'sales_flat_quote_address',
            'address_id'
        ),
        'address_id', $installer->getTable('sales_flat_quote_address'), 'address_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Flat Quote Shipping Rate');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_invoiced_aggregated'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_invoiced_aggregated'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Status')
    ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Orders Count')
    ->addColumn('orders_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Orders Invoiced')
    ->addColumn('invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Invoiced')
    ->addColumn('invoiced_captured', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Invoiced Captured')
    ->addColumn('invoiced_not_captured', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Invoiced Not Captured')
    ->addIndex(
        $installer->getIdxName(
            'sales_invoiced_aggregated',
            array('period', 'store_id', 'order_status'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'order_status'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_invoiced_aggregated', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_invoiced_aggregated', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Invoiced Aggregated');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_invoiced_aggregated_order'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_invoiced_aggregated_order'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable'  => false,
        'default'   => '',
        ), 'Order Status')
    ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Orders Count')
    ->addColumn('orders_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Orders Invoiced')
    ->addColumn('invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Invoiced')
    ->addColumn('invoiced_captured', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Invoiced Captured')
    ->addColumn('invoiced_not_captured', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Invoiced Not Captured')
    ->addIndex(
        $installer->getIdxName(
            'sales_invoiced_aggregated_order',
            array('period', 'store_id', 'order_status'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'order_status'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_invoiced_aggregated_order', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_invoiced_aggregated_order', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Invoiced Aggregated Order');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_order_aggregated_created'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_order_aggregated_created'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable'  => false,
        'default'   => '',
        ), 'Order Status')
    ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Orders Count')
    ->addColumn('total_qty_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Qty Ordered')
    ->addColumn('total_qty_invoiced', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Qty Invoiced')
    ->addColumn('total_income_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Income Amount')
    ->addColumn('total_revenue_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Revenue Amount')
    ->addColumn('total_profit_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Profit Amount')
    ->addColumn('total_invoiced_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Invoiced Amount')
    ->addColumn('total_canceled_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Canceled Amount')
    ->addColumn('total_paid_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Paid Amount')
    ->addColumn('total_refunded_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Refunded Amount')
    ->addColumn('total_tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Tax Amount')
    ->addColumn('total_tax_amount_actual', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Tax Amount Actual')
    ->addColumn('total_shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Shipping Amount')
    ->addColumn('total_shipping_amount_actual', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Shipping Amount Actual')
    ->addColumn('total_discount_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Discount Amount')
    ->addColumn('total_discount_amount_actual', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Total Discount Amount Actual')
    ->addIndex(
        $installer->getIdxName(
            'sales_order_aggregated_created',
            array('period', 'store_id', 'order_status'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'order_status'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_order_aggregated_created', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_order_aggregated_created', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Order Aggregated Created');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_payment_transaction'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_payment_transaction'))
    ->addColumn('transaction_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Transaction Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Parent Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Order Id')
    ->addColumn('payment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Payment Id')
    ->addColumn('txn_id', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        ), 'Txn Id')
    ->addColumn('parent_txn_id', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        ), 'Parent Txn Id')
    ->addColumn('txn_type', Varien_Db_Ddl_Table::TYPE_TEXT, 15, array(
        ), 'Txn Type')
    ->addColumn('is_closed', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
        ), 'Is Closed')
    ->addColumn('additional_information', Varien_Db_Ddl_Table::TYPE_BLOB, '64K', array(
        ), 'Additional Information')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Created At')
    ->addIndex(
        $installer->getIdxName(
            'sales_payment_transaction',
            array('order_id', 'payment_id', 'txn_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('order_id', 'payment_id', 'txn_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_payment_transaction', array('order_id')),
        array('order_id'))
    ->addIndex($installer->getIdxName('sales_payment_transaction', array('parent_id')),
        array('parent_id'))
    ->addIndex($installer->getIdxName('sales_payment_transaction', array('payment_id')),
        array('payment_id'))
    ->addForeignKey($installer->getFkName('sales_payment_transaction', 'order_id', 'sales_flat_order', 'entity_id'),
        'order_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'sales_payment_transaction',
            'parent_id',
            'sales_payment_transaction',
            'transaction_id'
        ),
        'parent_id', $installer->getTable('sales_payment_transaction'), 'transaction_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'sales_payment_transaction',
            'payment_id',
            'sales_flat_order_payment',
            'entity_id'
        ),
        'payment_id', $installer->getTable('sales_flat_order_payment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Payment Transaction');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_refunded_aggregated'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_refunded_aggregated'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        'nullable'  => false,
        'default'   => '',
        ), 'Order Status')
    ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Orders Count')
    ->addColumn('refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Refunded')
    ->addColumn('online_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Online Refunded')
    ->addColumn('offline_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Offline Refunded')
    ->addIndex(
        $installer->getIdxName(
            'sales_refunded_aggregated',
            array('period', 'store_id', 'order_status'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'order_status'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_refunded_aggregated', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_refunded_aggregated', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Refunded Aggregated');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_refunded_aggregated_order'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_refunded_aggregated_order'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Status')
    ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Orders Count')
    ->addColumn('refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Refunded')
    ->addColumn('online_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Online Refunded')
    ->addColumn('offline_refunded', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Offline Refunded')
    ->addIndex(
        $installer->getIdxName(
            'sales_refunded_aggregated_order',
            array('period', 'store_id', 'order_status'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'order_status'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_refunded_aggregated_order', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_refunded_aggregated_order', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Refunded Aggregated Order');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_shipping_aggregated'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_shipping_aggregated'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Status')
    ->addColumn('shipping_description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Shipping Description')
    ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Orders Count')
    ->addColumn('total_shipping', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Shipping')
    ->addColumn('total_shipping_actual', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Shipping Actual')
    ->addIndex(
        $installer->getIdxName(
            'sales_shipping_aggregated',
            array('period', 'store_id', 'order_status', 'shipping_description'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'order_status', 'shipping_description'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_shipping_aggregated', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_shipping_aggregated', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Shipping Aggregated');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_shipping_aggregated_order'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_shipping_aggregated_order'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('order_status', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
        ), 'Order Status')
    ->addColumn('shipping_description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Shipping Description')
    ->addColumn('orders_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Orders Count')
    ->addColumn('total_shipping', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Shipping')
    ->addColumn('total_shipping_actual', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Total Shipping Actual')
    ->addIndex(
        $installer->getIdxName(
            'sales_shipping_aggregated_order',
            array('period', 'store_id', 'order_status', 'shipping_description'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'order_status', 'shipping_description'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_shipping_aggregated_order', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_shipping_aggregated_order', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Shipping Aggregated Order');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_bestsellers_aggregated_daily'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_bestsellers_aggregated_daily'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Product Id')
    ->addColumn('product_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Product Name')
    ->addColumn('product_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Product Price')
    ->addColumn('qty_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Qty Ordered')
    ->addColumn('rating_pos', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Rating Pos')
    ->addIndex(
        $installer->getIdxName(
            'sales_bestsellers_aggregated_daily',
            array('period', 'store_id', 'product_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'product_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_bestsellers_aggregated_daily', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_bestsellers_aggregated_daily', array('product_id')),
        array('product_id'))
    ->addForeignKey($installer->getFkName('sales_bestsellers_aggregated_daily', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'sales_bestsellers_aggregated_daily',
            'product_id',
            'catalog_product_entity',
            'entity_id'
        ),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Bestsellers Aggregated Daily');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_bestsellers_aggregated_monthly'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_bestsellers_aggregated_monthly'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Product Id')
    ->addColumn('product_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Product Name')
    ->addColumn('product_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Product Price')
    ->addColumn('qty_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Qty Ordered')
    ->addColumn('rating_pos', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Rating Pos')
    ->addIndex(
        $installer->getIdxName(
            'sales_bestsellers_aggregated_monthly',
            array('period', 'store_id', 'product_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'product_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_bestsellers_aggregated_monthly', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_bestsellers_aggregated_monthly', array('product_id')),
        array('product_id'))
    ->addForeignKey(
        $installer->getFkName(
            'sales_bestsellers_aggregated_monthly',
            'store_id',
            'core_store',
            'store_id'
        ),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'sales_bestsellers_aggregated_monthly',
            'product_id',
            'catalog_product_entity',
            'entity_id'
        ),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Bestsellers Aggregated Monthly');
$installer->getConnection()->createTable($table);

/**
 * Create table 'sales_bestsellers_aggregated_yearly'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_bestsellers_aggregated_yearly'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('period', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        ), 'Period')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Product Id')
    ->addColumn('product_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Product Name')
    ->addColumn('product_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Product Price')
    ->addColumn('qty_ordered', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Qty Ordered')
    ->addColumn('rating_pos', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Rating Pos')
    ->addIndex(
        $installer->getIdxName(
            'sales_bestsellers_aggregated_yearly',
            array('period', 'store_id', 'product_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('period', 'store_id', 'product_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_bestsellers_aggregated_yearly', array('store_id')),
        array('store_id'))
    ->addIndex($installer->getIdxName('sales_bestsellers_aggregated_yearly', array('product_id')),
        array('product_id'))
    ->addForeignKey(
        $installer->getFkName(
            'sales_bestsellers_aggregated_yearly',
            'store_id',
            'core_store',
            'store_id'
        ),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'sales_bestsellers_aggregated_yearly',
            'product_id',
            'catalog_product_entity',
            'entity_id'
        ),
        'product_id', $installer->getTable('catalog_product_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Bestsellers Aggregated Yearly');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_billing_agreement'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_billing_agreement'))
    ->addColumn('agreement_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Agreement Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Customer Id')
    ->addColumn('method_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        ), 'Method Code')
    ->addColumn('reference_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        ), 'Reference Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable'  => false,
        ), 'Status')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('agreement_label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Agreement Label')
    ->addIndex($installer->getIdxName('sales_billing_agreement', array('customer_id')),
        array('customer_id'))
    ->addIndex($installer->getIdxName('sales_billing_agreement', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_billing_agreement', 'customer_id', 'customer_entity', 'entity_id'),
        'customer_id', $installer->getTable('customer_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_billing_agreement', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Billing Agreement');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_billing_agreement_order'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_billing_agreement_order'))
    ->addColumn('agreement_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Agreement Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Order Id')
    ->addIndex($installer->getIdxName('sales_billing_agreement_order', array('order_id')),
        array('order_id'))
    ->addForeignKey(
        $installer->getFkName(
            'sales_billing_agreement_order',
            'agreement_id',
            'sales_billing_agreement',
            'agreement_id'
        ),
        'agreement_id', $installer->getTable('sales_billing_agreement'), 'agreement_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_billing_agreement_order', 'order_id', 'sales_flat_order', 'entity_id'),
        'order_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Billing Agreement Order');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_recurring_profile'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_recurring_profile'))
    ->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Profile Id')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable'  => false,
        ), 'State')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Customer Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Store Id')
    ->addColumn('method_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        ), 'Method Code')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Updated At')
    ->addColumn('reference_id', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Reference Id')
    ->addColumn('subscriber_name', Varien_Db_Ddl_Table::TYPE_TEXT, 150, array(
        ), 'Subscriber Name')
    ->addColumn('start_datetime', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Start Datetime')
    ->addColumn('internal_reference_id', Varien_Db_Ddl_Table::TYPE_TEXT, 42, array(
        'nullable'  => false,
        ), 'Internal Reference Id')
    ->addColumn('schedule_description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Schedule Description')
    ->addColumn('suspension_threshold', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Suspension Threshold')
    ->addColumn('bill_failed_later', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Bill Failed Later')
    ->addColumn('period_unit', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        'nullable'  => false,
        ), 'Period Unit')
    ->addColumn('period_frequency', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Period Frequency')
    ->addColumn('period_max_cycles', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Period Max Cycles')
    ->addColumn('billing_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Billing Amount')
    ->addColumn('trial_period_unit', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
        ), 'Trial Period Unit')
    ->addColumn('trial_period_frequency', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Trial Period Frequency')
    ->addColumn('trial_period_max_cycles', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        ), 'Trial Period Max Cycles')
    ->addColumn('trial_billing_amount', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        ), 'Trial Billing Amount')
    ->addColumn('currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
        'nullable'  => false,
        ), 'Currency Code')
    ->addColumn('shipping_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Shipping Amount')
    ->addColumn('tax_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Tax Amount')
    ->addColumn('init_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Init Amount')
    ->addColumn('init_may_fail', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Init May Fail')
    ->addColumn('order_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
        ), 'Order Info')
    ->addColumn('order_item_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
        ), 'Order Item Info')
    ->addColumn('billing_address_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => false,
        ), 'Billing Address Info')
    ->addColumn('shipping_address_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Shipping Address Info')
    ->addColumn('profile_vendor_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Profile Vendor Info')
    ->addColumn('additional_info', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Additional Info')
    ->addIndex(
        $installer->getIdxName(
            'sales_recurring_profile',
            array('internal_reference_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('internal_reference_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_recurring_profile', array('customer_id')),
        array('customer_id'))
    ->addIndex($installer->getIdxName('sales_recurring_profile', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_recurring_profile', 'customer_id', 'customer_entity', 'entity_id'),
        'customer_id', $installer->getTable('customer_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_recurring_profile', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Recurring Profile');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_recurring_profile_order'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_recurring_profile_order'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Link Id')
    ->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Profile Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Order Id')
    ->addIndex(
        $installer->getIdxName(
            'sales_recurring_profile_order',
            array('profile_id', 'order_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('profile_id', 'order_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName('sales_recurring_profile_order', array('order_id')),
        array('order_id'))
    ->addForeignKey(
        $installer->getFkName(
            'sales_recurring_profile_order',
            'order_id',
            'sales_flat_order',
            'entity_id'
        ),
        'order_id', $installer->getTable('sales_flat_order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'sales_recurring_profile_order',
            'profile_id',
            'sales_recurring_profile',
            'profile_id'
        ),
        'profile_id', $installer->getTable('sales_recurring_profile'), 'profile_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Recurring Profile Order');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_order_tax'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_order_tax'))
    ->addColumn('tax_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Tax Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Order Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Code')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Title')
    ->addColumn('percent', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Percent')
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Amount')
    ->addColumn('priority', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Priority')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Position')
    ->addColumn('base_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Amount')
    ->addColumn('process', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        ), 'Process')
    ->addColumn('base_real_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        ), 'Base Real Amount')
    ->addColumn('hidden', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Hidden')
    ->addIndex($installer->getIdxName('sales_order_tax', array('order_id', 'priority', 'position')),
        array('order_id', 'priority', 'position'))
    ->setComment('Sales Order Tax Table');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_order_status'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_order_status'))
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Status')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
        ), 'Label')
    ->setComment('Sales Order Status Table');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_order_status_state'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_order_status_state'))
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Status')
    ->addColumn('state', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Label')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Default')
    ->addForeignKey($installer->getFkName('sales_order_status_state', 'status', 'sales_order_status', 'status'),
        'status', $installer->getTable('sales_order_status'), 'status',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Order Status Table');
$installer->getConnection()->createTable($table);


/**
 * Create table 'sales_order_status_label'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sales_order_status_label'))
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Status')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Id')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, 128, array(
        'nullable'  => false,
        ), 'Label')
    ->addIndex($installer->getIdxName('sales_order_status_label', array('store_id')),
        array('store_id'))
    ->addForeignKey($installer->getFkName('sales_order_status_label', 'status', 'sales_order_status', 'status'),
        'status', $installer->getTable('sales_order_status'), 'status',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sales_order_status_label', 'store_id', 'core_store', 'store_id'),
        'store_id', $installer->getTable('core_store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Sales Order Status Label Table');
$installer->getConnection()->createTable($table);


/**
 * Install eav entity types to the eav/entity_type table
 */
$installer->addEntityType('order', array(
    'entity_model'          => 'Mage_Sales_Model_Resource_Order',
    'table'                 => 'sales_flat_order',
    'increment_model'       => 'Mage_Eav_Model_Entity_Increment_Numeric',
    'increment_per_store'   => true
));

$installer->addEntityType('invoice', array(
    'entity_model'          => 'Mage_Sales_Model_Resource_Order_Invoice',
    'table'                 => 'sales_flat_invoice',
    'increment_model'       => 'Mage_Eav_Model_Entity_Increment_Numeric',
    'increment_per_store'   => true
));

$installer->addEntityType('creditmemo', array(
    'entity_model'          => 'Mage_Sales_Model_Resource_Order_Creditmemo',
    'table'                 => 'sales_flat_creditmemo',
    'increment_model'       => 'Mage_Eav_Model_Entity_Increment_Numeric',
    'increment_per_store'   => true
));

$installer->addEntityType('shipment', array(
    'entity_model'          => 'Mage_Sales_Model_Resource_Order_Shipment',
    'table'                 => 'sales_flat_shipment',
    'increment_model'       => 'Mage_Eav_Model_Entity_Increment_Numeric',
    'increment_per_store'   => true
));

$installer->endSetup();
