<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $connection = $installer->getConnection(self::$connectionName);
            //sales_bestsellers_aggregated_daily
            $connection->dropForeignKey(
                $installer->getTable('sales_bestsellers_aggregated_daily', self::$connectionName),
                $installer->getFkName(
                    'sales_bestsellers_aggregated_daily',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id',
                    self::$connectionName
                )
            );
            //sales_bestsellers_aggregated_monthly
            $connection->dropForeignKey(
                $installer->getTable('sales_bestsellers_aggregated_monthly', self::$connectionName),
                $installer->getFkName(
                    'sales_bestsellers_aggregated_monthly',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id',
                    self::$connectionName
                )
            );

            //sales_bestsellers_aggregated_yearly
            $connection->dropForeignKey(
                $installer->getTable('sales_bestsellers_aggregated_yearly', self::$connectionName),
                $installer->getFkName(
                    'sales_bestsellers_aggregated_yearly',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id',
                    self::$connectionName
                )
            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->addColumnBaseGrandTotal($installer);
            $this->addIndexBaseGrandTotal($installer);
        }
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $tables = [
                'sales_invoice_grid',
                'sales_order',
                'sales_shipment_grid',
            ];
            foreach ($tables as $table) {
                $salesConnection = $setup->getConnection(self::$connectionName);
                $salesConnection->modifyColumn(
                    $installer->getTable($table, self::$connectionName),
                    'customer_group_id',
                    ['type' => 'integer']
                );
            }
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $connection = $installer->getConnection(self::$connectionName);
            $connection->modifyColumn(
                $installer->getTable('sales_order_payment', self::$connectionName),
                'cc_number_enc',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 128
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function addColumnBaseGrandTotal(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection(self::$connectionName);
        $connection->addColumn(
            $installer->getTable('sales_invoice_grid', self::$connectionName),
            'base_grand_total',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Base Grand Total',
                'after' => 'grand_total'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function addIndexBaseGrandTotal(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection(self::$connectionName);
        $connection->addIndex(
            $installer->getTable('sales_invoice_grid', self::$connectionName),
            $installer->getIdxName('sales_invoice_grid', ['base_grand_total'], '', self::$connectionName),
            ['base_grand_total']
        );
    }
}
