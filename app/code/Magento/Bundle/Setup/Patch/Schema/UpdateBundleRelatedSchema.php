<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * Class UpdateBundleRelatedSchema
 *
 * @package Magento\Bundle\Setup\Patch
 */
class UpdateBundleRelatedSchema implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * UpdateBundleRelatedSchema constructor.
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        // Updating data of the 'catalog_product_bundle_option_value' table.
        $tableName = $this->schemaSetup->getTable('catalog_product_bundle_option_value');

        $select = $this->schemaSetup->getConnection()->select()
            ->from(
                ['values' => $tableName],
                ['value_id']
            )->joinLeft(
                [
                    'options' => $this->schemaSetup->getTable(
                        'catalog_product_bundle_option'
                    )
                ],
                'values.option_id = options.option_id',
                ['parent_product_id' => 'parent_id']
            );

        $this->schemaSetup->getConnection()->query(
            $this->schemaSetup->getConnection()->insertFromSelect(
                $select,
                $tableName,
                ['value_id', 'parent_product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            )
        );

        // Updating data of the 'catalog_product_bundle_selection_price' table.
        $tableName = $this->schemaSetup->getTable(
            'catalog_product_bundle_selection_price'
        );
        $tmpTableName = $this->schemaSetup->getTable(
            'catalog_product_bundle_selection_price_tmp'
        );

        $existingForeignKeys = $this->schemaSetup->getConnection()->getForeignKeys($tableName);

        foreach ($existingForeignKeys as $key) {
            $this->schemaSetup->getConnection()->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
        }

        $this->schemaSetup->getConnection()->createTable(
            $this->schemaSetup->getConnection()->createTableByDdl($tableName, $tmpTableName)
        );

        foreach ($existingForeignKeys as $key) {
            $this->schemaSetup->getConnection()->addForeignKey(
                $key['FK_NAME'],
                $key['TABLE_NAME'],
                $key['COLUMN_NAME'],
                $key['REF_TABLE_NAME'],
                $key['REF_COLUMN_NAME'],
                $key['ON_DELETE']
            );
        }

        $this->schemaSetup->getConnection()->query(
            $this->schemaSetup->getConnection()->insertFromSelect(
                $this->schemaSetup->getConnection()->select()->from($tableName),
                $tmpTableName
            )
        );

        $this->schemaSetup->getConnection()->truncateTable($tableName);

        $columnsToSelect = [];

        foreach ($this->schemaSetup->getConnection()->describeTable($tmpTableName) as $column) {
            $alias = $column['COLUMN_NAME'] == 'parent_product_id' ? 'selections.' : 'prices.';

            $columnsToSelect[] = $alias . $column['COLUMN_NAME'];
        }

        $select = $this->schemaSetup->getConnection()->select()
            ->from(
                ['prices' => $tmpTableName],
                []
            )->joinLeft(
                [
                    'selections' => $this->schemaSetup->getTable(
                        'catalog_product_bundle_selection'
                    )
                ],
                'prices.selection_id = selections.selection_id',
                []
            )->columns($columnsToSelect);

        $this->schemaSetup->getConnection()->query(
            $this->schemaSetup->getConnection()->insertFromSelect($select, $tableName)
        );

        $this->schemaSetup->getConnection()->dropTable($tmpTableName);

        $this->schemaSetup->endSetup();
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
    public static function getVersion()
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
