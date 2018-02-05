<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Setup\Patch;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch204
{


    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Updating data of the 'catalog_product_bundle_option_value' table.
        $tableName = $setup->getTable('catalog_product_bundle_option_value');

        $select = $setup->getConnection()->select()
            ->from(
                ['values' => $tableName],
                ['value_id']
            )->joinLeft(
                ['options' => $setup->getTable('catalog_product_bundle_option')],
                'values.option_id = options.option_id',
                ['parent_product_id' => 'parent_id']
            );
        $setup->getConnection()->query(
            $setup->getConnection()->insertFromSelect(
                $select,
                $tableName,
                ['value_id', 'parent_product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            )
        );
        // Updating data of the 'catalog_product_bundle_selection_price' table.
        $tableName = $setup->getTable('catalog_product_bundle_selection_price');
        $tmpTableName = $setup->getTable('catalog_product_bundle_selection_price_tmp');
        $existingForeignKeys = $setup->getConnection()->getForeignKeys($tableName);
        foreach ($existingForeignKeys as $key) {
            $setup->getConnection()->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
        }
        $setup->getConnection()->createTable(
            $setup->getConnection()->createTableByDdl($tableName, $tmpTableName)
        );
        foreach ($existingForeignKeys as $key) {
            $setup->getConnection()->addForeignKey(
                $key['FK_NAME'],
                $key['TABLE_NAME'],
                $key['COLUMN_NAME'],
                $key['REF_TABLE_NAME'],
                $key['REF_COLUMN_NAME'],
                $key['ON_DELETE']
            );
        }
        $setup->getConnection()->query(
            $setup->getConnection()->insertFromSelect(
                $setup->getConnection()->select()->from($tableName),
                $tmpTableName
            )
        );
        $setup->getConnection()->truncateTable($tableName);
        $columnsToSelect = [];
        foreach ($setup->getConnection()->describeTable($tmpTableName) as $column) {
            $alias = $column['COLUMN_NAME'] == 'parent_product_id' ? 'selections.' : 'prices.';
            $columnsToSelect[] = $alias . $column['COLUMN_NAME'];
        }
        $select = $setup->getConnection()->select()
            ->from(
                ['prices' => $tmpTableName],
                []
            )->joinLeft(
                ['selections' => $setup->getTable('catalog_product_bundle_selection')],
                'prices.selection_id = selections.selection_id',
                []
            )->columns($columnsToSelect);
        $setup->getConnection()->query(
            $setup->getConnection()->insertFromSelect($select, $tableName)
        );
        $setup->getConnection()->dropTable($tmpTableName);


        $setup->endSetup();

    }

}
