<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {

            $installer = $setup;

            /**
             * update columns created_at and updated_at in sales entities tables
             */

            $tables = [
                'sales_creditmemo',
                'sales_creditmemo_comment',
                'sales_invoice',
                'sales_invoice_comment',
                'sales_order',
                'sales_order_item',
                'sales_order_status_history',
                'sales_payment_transaction',
                'sales_shipment',
                'sales_shipment_comment',
                'sales_shipment_track'
            ];
            /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
            $connection = $installer->getConnection();
            foreach ($tables as $table) {
                $columns = $connection->describeTable($installer->getTable($table));
                if (isset($columns['created_at'])) {
                    $createdAt = $columns['created_at'];
                    $createdAt['DEFAULT'] = Table::TIMESTAMP_INIT;
                    $createdAt['TYPE'] = Table::TYPE_TIMESTAMP;
                    $connection->modifyColumn($installer->getTable($table), 'created_at', $createdAt);
                }
                if (isset($columns['updated_at'])) {
                    $updatedAt = $columns['updated_at'];
                    $updatedAt['DEFAULT'] = Table::TIMESTAMP_UPDATE;
                    $updatedAt['TYPE'] = Table::TYPE_TIMESTAMP;
                    $connection->modifyColumn($installer->getTable($table), 'updated_at', $updatedAt);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {

            /**
             * Adding 'updated_at' columns.
             */

            $tables = ['sales_shipment_grid', 'sales_invoice_grid', 'sales_creditmemo_grid'];

            foreach ($tables as $table) {
                $table = $setup->getTable($table);

                $setup->getConnection()
                    ->addColumn(
                        $table,
                        'updated_at',
                        [
                            'type' => Table::TYPE_TIMESTAMP,
                            'after' => 'created_at',
                            'comment' => 'Updated At'
                        ]
                    );

                $setup->getConnection()
                    ->addIndex($table, $setup->getIdxName($table, ['updated_at']), 'updated_at');
            }

            /**
             * Modifying default value of 'updated_at' columns.
             */

            $tables = ['sales_order', 'sales_shipment', 'sales_invoice', 'sales_creditmemo'];

            foreach ($tables as $table) {
                $table = $setup->getTable($table);

                $setup->getConnection()
                    ->modifyColumn(
                        $table,
                        'updated_at',
                        [
                            'type' => Table::TYPE_TIMESTAMP,
                            'default' => Table::TIMESTAMP_INIT_UPDATE
                        ]
                    );
            }
        }

        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            $dropIncrementIndexTables = [
                'sales_creditmemo',
                'sales_invoice',
                'sales_order',
                'sales_shipment',
                'sales_creditmemo_grid',
                'sales_invoice_grid',
                'sales_order_grid',
                'sales_shipment_grid',
            ];
            foreach ($dropIncrementIndexTables as $table) {
                $connection->dropIndex(
                    $installer->getTable($table),
                    $installer->getIdxName(
                        $installer->getTable($table),
                        ['increment_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    )
                );
            }
            $createIncrementIndexTables = [
                'sales_creditmemo',
                'sales_invoice',
                'sales_order',
                'sales_shipment',
                'sales_creditmemo_grid',
                'sales_invoice_grid',
                'sales_order_grid',
                'sales_shipment_grid',
            ];
            foreach ($createIncrementIndexTables as $table) {
                $connection->addIndex(
                    $installer->getTable($table),
                    $installer->getIdxName(
                        $installer->getTable($table),
                        ['increment_id', 'store_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['increment_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                );
            }
        }

        if (version_compare($context->getVersion(), '2.0.4') < 0) {

            /**
             * Adding 'send_email' columns.
             */

            $tables = ['sales_order', 'sales_invoice', 'sales_shipment', 'sales_creditmemo'];

            foreach ($tables as $table) {
                $table = $setup->getTable($table);

                $setup->getConnection()
                    ->addColumn(
                        $table,
                        'send_email',
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'after' => 'email_sent',
                            'comment' => 'Send Email',
                            'unsigned' => true
                        ]
                    );

                $setup->getConnection()
                    ->addIndex($table, $setup->getIdxName($table, ['email_sent']), 'email_sent');

                $setup->getConnection()
                    ->addIndex($table, $setup->getIdxName($table, ['send_email']), 'send_email');
            }

            /**
             * Adding 'customer_note' columns.
             */

            $tables = ['sales_invoice', 'sales_shipment', 'sales_creditmemo'];

            foreach ($tables as $table) {
                $table = $setup->getTable($table);

                $setup->getConnection()
                    ->addColumn(
                        $table,
                        'customer_note',
                        [
                            'type' => Table::TYPE_TEXT,
                            'after' => 'updated_at',
                            'comment' => 'Customer Note'
                        ]
                    );
            }

            /**
             * Adding 'customer_note_notify' columns.
             */

            $tables = ['sales_invoice', 'sales_shipment', 'sales_creditmemo'];

            foreach ($tables as $table) {
                $table = $setup->getTable($table);

                $setup->getConnection()
                    ->addColumn(
                        $table,
                        'customer_note_notify',
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'after' => 'customer_note',
                            'comment' => 'Customer Note Notify',
                            'unsigned' => true
                        ]
                    );
            }
        }
    }
}
