<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Setup\Patch;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\VersionedDataPatch;

/**
 * Class UpdateBundleRelatedSchema
 * 
 * @package Magento\Bundle\Setup\Patch
 */
class UpdateBundleRelatedTables implements DataPatchInterface, VersionedDataPatch
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {

        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        // Updating data of the 'catalog_product_bundle_option_value' table.
        $tableName = $this->moduleDataSetup->getTable('catalog_product_bundle_option_value');

        $select = $this->moduleDataSetup->getConnection()->select()
            ->from(
                ['values' => $tableName],
                ['value_id']
            )->joinLeft(
                ['options' => $this->moduleDataSetup->getTable('catalog_product_bundle_option')],
                'values.option_id = options.option_id',
                ['parent_product_id' => 'parent_id']
            );

        $this->moduleDataSetup->getConnection()->query(
            $this->moduleDataSetup->getConnection()->insertFromSelect(
                $select,
                $tableName,
                ['value_id', 'parent_product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            )
        );

        // Updating data of the 'catalog_product_bundle_selection_price' table.
        $tableName = $this->moduleDataSetup->getTable('catalog_product_bundle_selection_price');
        $tmpTableName = $this->moduleDataSetup->getTable('catalog_product_bundle_selection_price_tmp');

        $existingForeignKeys = $this->moduleDataSetup->getConnection()->getForeignKeys($tableName);

        foreach ($existingForeignKeys as $key) {
            $this->moduleDataSetup->getConnection()->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
        }

        $this->moduleDataSetup->getConnection()->createTable(
            $this->moduleDataSetup->getConnection()->createTableByDdl($tableName, $tmpTableName)
        );

        foreach ($existingForeignKeys as $key) {
            $this->moduleDataSetup->getConnection()->addForeignKey(
                $key['FK_NAME'],
                $key['TABLE_NAME'],
                $key['COLUMN_NAME'],
                $key['REF_TABLE_NAME'],
                $key['REF_COLUMN_NAME'],
                $key['ON_DELETE']
            );
        }

        $this->moduleDataSetup->getConnection()->query(
            $this->moduleDataSetup->getConnection()->insertFromSelect(
                $this->moduleDataSetup->getConnection()->select()->from($tableName),
                $tmpTableName
            )
        );

        $this->moduleDataSetup->getConnection()->truncateTable($tableName);

        $columnsToSelect = [];

        foreach ($this->moduleDataSetup->getConnection()->describeTable($tmpTableName) as $column) {
            $alias = $column['COLUMN_NAME'] == 'parent_product_id' ? 'selections.' : 'prices.';

            $columnsToSelect[] = $alias . $column['COLUMN_NAME'];
        }

        $select = $this->moduleDataSetup->getConnection()->select()
            ->from(
                ['prices' => $tmpTableName],
                []
            )->joinLeft(
                ['selections' => $this->moduleDataSetup->getTable('catalog_product_bundle_selection')],
                'prices.selection_id = selections.selection_id',
                []
            )->columns($columnsToSelect);

        $this->moduleDataSetup->getConnection()->query(
            $this->moduleDataSetup->getConnection()->insertFromSelect($select, $tableName)
        );

        $this->moduleDataSetup->getConnection()->dropTable($tmpTableName);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.4';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
