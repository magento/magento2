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
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
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
    }
}
