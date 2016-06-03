<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $connection = $installer->getConnection();
            //sales_bestsellers_aggregated_daily
            $connection->dropForeignKey(
                $installer->getTable('sales_bestsellers_aggregated_daily'),
                $installer->getFkName(
                    'sales_bestsellers_aggregated_daily',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id')
            );
            //sales_bestsellers_aggregated_monthly
            $connection->dropForeignKey(
                $installer->getTable('sales_bestsellers_aggregated_monthly'),
                $installer->getFkName(
                    'sales_bestsellers_aggregated_monthly',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id')
            );

            //sales_bestsellers_aggregated_yearly
            $connection->dropForeignKey(
                $installer->getTable('sales_bestsellers_aggregated_yearly'),
                $installer->getFkName(
                    'sales_bestsellers_aggregated_yearly',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id')
            );

            $installer->endSetup();
        }
    }
}