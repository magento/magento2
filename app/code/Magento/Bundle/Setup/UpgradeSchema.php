<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $fields = [
                ['table' => 'catalog_product_index_price_bundle_opt_idx', 'column' => 'alt_group_price'],
                ['table' => 'catalog_product_index_price_bundle_opt_tmp', 'column' => 'alt_group_price'],
                ['table' => 'catalog_product_index_price_bundle_idx', 'column' => 'base_group_price'],
                ['table' => 'catalog_product_index_price_bundle_tmp', 'column' => 'base_group_price'],
                ['table' => 'catalog_product_index_price_bundle_idx', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_bundle_opt_idx', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_bundle_opt_tmp', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_bundle_sel_idx', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_bundle_sel_tmp', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_bundle_tmp', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_bundle_idx', 'column' => 'group_price_percent'],
                ['table' => 'catalog_product_index_price_bundle_tmp', 'column' => 'group_price_percent'],
            ];

            foreach ($fields as $filedInfo) {
                $connection->dropColumn($setup->getTable($filedInfo['table']), $filedInfo['column']);
            }
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $tables = [
                'catalog_product_index_price_bundle_idx',
                'catalog_product_index_price_bundle_opt_idx',
                'catalog_product_index_price_bundle_opt_tmp',
                'catalog_product_index_price_bundle_sel_idx',
                'catalog_product_index_price_bundle_sel_tmp',
                'catalog_product_index_price_bundle_tmp',
            ];
            foreach ($tables as $table) {
                $setup->getConnection()->modifyColumn(
                    $setup->getTable($table),
                    'customer_group_id',
                    ['type' => 'integer', 'nullable' => false]
                );
            }
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            // Updating the 'catalog_product_bundle_option_value' table.
            $connection->addColumn(
                $setup->getTable('catalog_product_bundle_option_value'),
                'parent_product_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'Parent Product Id',
                    'after' => 'option_id'
                ]
            );

            $existingForeignKeys = $connection->getForeignKeys(
                $setup->getTable('catalog_product_bundle_option_value')
            );

            foreach ($existingForeignKeys as $key) {
                $connection->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
            }

            $connection->dropIndex(
                $setup->getTable('catalog_product_bundle_option_value'),
                $setup->getIdxName(
                    $setup->getTable('catalog_product_bundle_option_value'),
                    ['option_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                )
            );

            $connection->addIndex(
                $setup->getTable('catalog_product_bundle_option_value'),
                $setup->getIdxName(
                    $setup->getTable('catalog_product_bundle_option_value'),
                    ['option_id', 'parent_product_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['option_id', 'parent_product_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );

            foreach ($existingForeignKeys as $key) {
                $connection->addForeignKey(
                    $key['FK_NAME'],
                    $key['TABLE_NAME'],
                    $key['COLUMN_NAME'],
                    $key['REF_TABLE_NAME'],
                    $key['REF_COLUMN_NAME'],
                    $key['ON_DELETE']
                );
            }

            // Updating the 'catalog_product_bundle_selection_price' table.
            $connection->addColumn(
                $setup->getTable('catalog_product_bundle_selection_price'),
                'parent_product_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'Parent Product Id',
                    'after' => 'selection_id'
                ]
            );

            $existingForeignKeys = $connection->getForeignKeys(
                $setup->getTable('catalog_product_bundle_selection_price')
            );

            foreach ($existingForeignKeys as $key) {
                $connection->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
            }

            $connection->dropIndex(
                $setup->getTable('catalog_product_bundle_selection_price'),
                $connection->getPrimaryKeyName(
                    $setup->getTable('catalog_product_bundle_selection_price')
                )
            );

            $connection->addIndex(
                $setup->getTable('catalog_product_bundle_selection_price'),
                $connection->getPrimaryKeyName(
                    $setup->getTable('catalog_product_bundle_selection_price')
                ),
                ['selection_id', 'parent_product_id', 'website_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY
            );

            foreach ($existingForeignKeys as $key) {
                $connection->addForeignKey(
                    $key['FK_NAME'],
                    $key['TABLE_NAME'],
                    $key['COLUMN_NAME'],
                    $key['REF_TABLE_NAME'],
                    $key['REF_COLUMN_NAME'],
                    $key['ON_DELETE']
                );
            }
        }

        $setup->endSetup();
    }
}
