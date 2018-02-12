<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Setup\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpdateBundleRelatedSchema
 *
 * @package Magento\Bundle\Setup\Patch
 */
class UpdateBundleRelatedSchema implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * UpdateBundleRelatedSchema constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        // Updating data of the 'catalog_product_bundle_option_value' table.
        $tableName = $this->resourceConnection->getConnection()->getTableName('catalog_product_bundle_option_value');

        $select = $this->resourceConnection->getConnection()->select()
            ->from(
                ['values' => $tableName],
                ['value_id']
            )->joinLeft(
                [
                    'options' => $this->resourceConnection->getConnection()->getTableName(
                        'catalog_product_bundle_option'
                    )
                ],
                'values.option_id = options.option_id',
                ['parent_product_id' => 'parent_id']
            );

        $this->resourceConnection->getConnection()->query(
            $this->resourceConnection->getConnection()->insertFromSelect(
                $select,
                $tableName,
                ['value_id', 'parent_product_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            )
        );

        // Updating data of the 'catalog_product_bundle_selection_price' table.
        $tableName = $this->resourceConnection->getConnection()->getTableName(
            'catalog_product_bundle_selection_price'
        );
        $tmpTableName = $this->resourceConnection->getConnection()->getTableName(
            'catalog_product_bundle_selection_price_tmp'
        );

        $existingForeignKeys = $this->resourceConnection->getConnection()->getForeignKeys($tableName);

        foreach ($existingForeignKeys as $key) {
            $this->resourceConnection->getConnection()->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
        }

        $this->resourceConnection->getConnection()->createTable(
            $this->resourceConnection->getConnection()->createTableByDdl($tableName, $tmpTableName)
        );

        foreach ($existingForeignKeys as $key) {
            $this->resourceConnection->getConnection()->addForeignKey(
                $key['FK_NAME'],
                $key['TABLE_NAME'],
                $key['COLUMN_NAME'],
                $key['REF_TABLE_NAME'],
                $key['REF_COLUMN_NAME'],
                $key['ON_DELETE']
            );
        }

        $this->resourceConnection->getConnection()->query(
            $this->resourceConnection->getConnection()->insertFromSelect(
                $this->resourceConnection->getConnection()->select()->from($tableName),
                $tmpTableName
            )
        );

        $this->resourceConnection->getConnection()->truncateTable($tableName);

        $columnsToSelect = [];

        $this->resourceConnection->getConnection()->startSetup();

        foreach ($this->resourceConnection->getConnection()->describeTable($tmpTableName) as $column) {
            $alias = $column['COLUMN_NAME'] == 'parent_product_id' ? 'selections.' : 'prices.';

            $columnsToSelect[] = $alias . $column['COLUMN_NAME'];
        }

        $select = $this->resourceConnection->getConnection()->select()
            ->from(
                ['prices' => $tmpTableName],
                []
            )->joinLeft(
                [
                    'selections' => $this->resourceConnection->getConnection()->getTableName(
                        'catalog_product_bundle_selection'
                    )
                ],
                'prices.selection_id = selections.selection_id',
                []
            )->columns($columnsToSelect);

        $this->resourceConnection->getConnection()->query(
            $this->resourceConnection->getConnection()->insertFromSelect($select, $tableName)
        );

        $this->resourceConnection->getConnection()->dropTable($tmpTableName);

        $this->resourceConnection->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateBundleRelatedEntityTytpes::class,
        ];
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
