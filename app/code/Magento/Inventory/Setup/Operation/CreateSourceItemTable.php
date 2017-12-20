<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class CreateSourceItemTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $sourceItemTable = $this->createSourceItemTable($setup);

        $setup->getConnection()->createTable($sourceItemTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createSourceItemTable(SchemaSetupInterface $setup): Table
    {
        $sourceItemTable = $setup->getTable(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);
        $sourceTable = $setup->getTable(SourceResourceModel::TABLE_NAME_SOURCE);

        return $setup->getConnection()->newTable(
            $sourceItemTable
        )->setComment(
            'Inventory Source item Table'
        )->addColumn(
            SourceItemResourceModel::ID_FIELD_NAME,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Source Item ID'
        )->addColumn(
            SourceItemInterface::SOURCE_CODE,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Source Code'
        )->addColumn(
            SourceItemInterface::SKU,
            Table::TYPE_TEXT,
            64,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Sku'
        )->addColumn(
            SourceItemInterface::QUANTITY,
            Table::TYPE_DECIMAL,
            null,
            [
                Table::OPTION_UNSIGNED => false,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
                Table::OPTION_PRECISION => 10,
                Table::OPTION_SCALE => 4,
            ],
            'Quantity'
        )->addColumn(
            SourceItemInterface::STATUS,
            Table::TYPE_SMALLINT,
            null,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => SourceItemInterface::STATUS_OUT_OF_STOCK,
                Table::OPTION_UNSIGNED => true,
            ],
            'Status'
        )->addForeignKey(
            $setup->getFkName(
                $sourceItemTable,
                SourceItemInterface::SOURCE_CODE,
                $sourceTable,
                SourceInterface::CODE
            ),
            SourceItemInterface::SOURCE_CODE,
            $sourceTable,
            SourceInterface::CODE,
            AdapterInterface::FK_ACTION_CASCADE
        )->addIndex(
            $setup->getIdxName(
                $sourceItemTable,
                [
                    SourceItemInterface::SOURCE_CODE,
                    SourceItemInterface::SKU,
                ],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [
                SourceItemInterface::SOURCE_CODE,
                SourceItemInterface::SKU,
            ],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
    }
}
