<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the CatalogRule module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->removeSubProductDiscounts($setup);
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $tables = [
                'catalogrule_product',
                'catalogrule_product_price',
            ];
            foreach ($tables as $table) {
                $setup->getConnection()->modifyColumn(
                    $setup->getTable($table),
                    'customer_group_id',
                    ['type' => 'integer']
                );
            }
        }

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $connection = $setup->getConnection();
            $connection->dropForeignKey(
                $setup->getTable('catalogrule_group_website'),
                $setup->getFkName(
                    'catalogrule_group_website',
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                )
            );
            $connection->dropForeignKey(
                $setup->getTable('catalogrule_group_website'),
                $setup->getFkName('catalogrule_group_website', 'rule_id', 'catalogrule', 'rule_id')
            );
            $connection->dropForeignKey(
                $setup->getTable('catalogrule_group_website'),
                $setup->getFkName('catalogrule_group_website', 'website_id', 'store_website', 'website_id')
            );

            $this->addReplicaTable($setup, 'catalogrule_product', 'catalogrule_product_replica');
            $this->addReplicaTable($setup, 'catalogrule_product_price', 'catalogrule_product_price_replica');
            $this->addReplicaTable($setup, 'catalogrule_group_website', 'catalogrule_group_website_replica');
        }

        $setup->endSetup();
    }

    /**
     * Remove Sub Product Discounts
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function removeSubProductDiscounts(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $data = [
            'catalogrule' => [
                'sub_is_enable',
                'sub_simple_action',
                'sub_discount_amount',
            ],
            'catalogrule_product' => [
                'sub_simple_action',
                'sub_discount_amount',
            ],
        ];

        foreach ($data as $table => $fields) {
            foreach ($fields as $field) {
                $connection->dropColumn($setup->getTable($table), $field);
            }
        }
    }

    /**
     * Add the replica table for existing one.
     *
     * @param SchemaSetupInterface $setup
     * @param string $existingTable
     * @param string $replicaTable
     * @return void
     */
    private function addReplicaTable(SchemaSetupInterface $setup, $existingTable, $replicaTable)
    {
        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS %s LIKE %s',
            $setup->getTable($replicaTable),
            $setup->getTable($existingTable)
        );
        $setup->getConnection()->query($sql);
    }
}
