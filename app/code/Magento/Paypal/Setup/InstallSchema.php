<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Prepare database for install
         */
        $installer->startSetup();

        /**
         * Create table 'paypal_billing_agreement'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('paypal_billing_agreement')
        )->addColumn(
            'agreement_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Agreement Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Id'
        )->addColumn(
            'method_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false],
            'Method Code'
        )->addColumn(
            'reference_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false],
            'Reference Id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => false],
            'Status'
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
            [],
            'Updated At'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Store Id'
        )->addColumn(
            'agreement_label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Agreement Label'
        )->addIndex(
            $installer->getIdxName('paypal_billing_agreement', ['customer_id']),
            ['customer_id']
        )->addIndex(
            $installer->getIdxName('paypal_billing_agreement', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('paypal_billing_agreement', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('paypal_billing_agreement', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Sales Billing Agreement'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'paypal_billing_agreement_order'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('paypal_billing_agreement_order')
        )->addColumn(
            'agreement_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Agreement Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Order Id'
        )->addIndex(
            $installer->getIdxName('paypal_billing_agreement_order', ['order_id']),
            ['order_id']
        )->addForeignKey(
            $installer->getFkName(
                'paypal_billing_agreement_order',
                'agreement_id',
                'paypal_billing_agreement',
                'agreement_id'
            ),
            'agreement_id',
            $installer->getTable('paypal_billing_agreement'),
            'agreement_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('paypal_billing_agreement_order', 'order_id', 'sales_order', 'entity_id'),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales Billing Agreement Order'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'paypal_settlement_report'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('paypal_settlement_report')
        )->addColumn(
            'report_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Report Id'
        )->addColumn(
            'report_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            [],
            'Report Date'
        )->addColumn(
            'account_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            [],
            'Account Id'
        )->addColumn(
            'filename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            24,
            [],
            'Filename'
        )->addColumn(
            'last_modified',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Last Modified'
        )->addIndex(
            $installer->getIdxName(
                'paypal_settlement_report',
                ['report_date', 'account_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['report_date', 'account_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Paypal Settlement Report Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'paypal_settlement_report_row'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('paypal_settlement_report_row')
        )->addColumn(
            'row_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Row Id'
        )->addColumn(
            'report_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Report Id'
        )->addColumn(
            'transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            19,
            [],
            'Transaction Id'
        )->addColumn(
            'invoice_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            [],
            'Invoice Id'
        )->addColumn(
            'paypal_reference_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            19,
            [],
            'Paypal Reference Id'
        )->addColumn(
            'paypal_reference_id_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            [],
            'Paypal Reference Id Type'
        )->addColumn(
            'transaction_event_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            5,
            [],
            'Transaction Event Code'
        )->addColumn(
            'transaction_initiation_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Transaction Initiation Date'
        )->addColumn(
            'transaction_completion_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Transaction Completion Date'
        )->addColumn(
            'transaction_debit_or_credit',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            2,
            ['nullable' => false, 'default' => 'CR'],
            'Transaction Debit Or Credit'
        )->addColumn(
            'gross_transaction_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '20,6',
            ['nullable' => false, 'default' => '0.000000'],
            'Gross Transaction Amount'
        )->addColumn(
            'gross_transaction_currency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            ['default' => false],
            'Gross Transaction Currency'
        )->addColumn(
            'fee_debit_or_credit',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            2,
            [],
            'Fee Debit Or Credit'
        )->addColumn(
            'fee_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '20,6',
            ['nullable' => false, 'default' => '0.000000'],
            'Fee Amount'
        )->addColumn(
            'fee_currency',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            [],
            'Fee Currency'
        )->addColumn(
            'custom_field',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Custom Field'
        )->addColumn(
            'consumer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            127,
            [],
            'Consumer Id'
        )->addColumn(
            'payment_tracking_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Payment Tracking ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            [],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('paypal_settlement_report_row', ['report_id']),
            ['report_id']
        )->addForeignKey(
            $installer->getFkName('paypal_settlement_report_row', 'report_id', 'paypal_settlement_report', 'report_id'),
            'report_id',
            $installer->getTable('paypal_settlement_report'),
            'report_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Paypal Settlement Report Row Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'paypal_cert'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('paypal_cert')
        )->addColumn(
            'cert_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Cert Id'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Website Id'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64K',
            [],
            'Content'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Updated At'
        )->addIndex(
            $installer->getIdxName('paypal_cert', ['website_id']),
            ['website_id']
        )->addForeignKey(
            $installer->getFkName('paypal_cert', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Paypal Certificate Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'paypal_payment_transaction'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('paypal_payment_transaction')
        )->addColumn(
            'transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'txn_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'Txn Id'
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
            $installer->getIdxName(
                'paypal_payment_transaction',
                ['txn_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['txn_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'PayPal Payflow Link Payment Transaction'
        );
        $installer->getConnection()->createTable($table);
        /**
         * Prepare database after install
         */
        $installer->endSetup();
    }
}
