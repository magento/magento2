<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

class CreateStockSourceLinkTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $stockSourceLinkTable = $this->createStockSourceLinkTable($setup);

        $setup->getConnection()->createTable($stockSourceLinkTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createStockSourceLinkTable(SchemaSetupInterface $setup): Table
    {
        $stockSourceLinkTable = $setup->getTable(StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK);
        $stockTable = $setup->getTable(StockResourceModel::TABLE_NAME_STOCK);
        $sourceTable = $setup->getTable(SourceResourceModel::TABLE_NAME_SOURCE);

        return $setup->getConnection()->newTable(
            $stockSourceLinkTable
        )->setComment(
            'Inventory Source Stock Link Table'
        )->addColumn(
            'link_id',
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Link ID'
        )->addColumn(
            StockSourceLink::STOCK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_UNSIGNED => true,
            ],
            'Stock ID'
        )->addColumn(
            StockSourceLink::SOURCE_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_UNSIGNED => true,
            ],
            'Source ID'
        )->addForeignKey(
            $setup->getFkName(
                $stockSourceLinkTable,
                StockSourceLink::STOCK_ID,
                $stockTable,
                StockInterface::STOCK_ID
            ),
            StockSourceLink::STOCK_ID,
            $stockTable,
            StockInterface::STOCK_ID,
            AdapterInterface::FK_ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                $stockSourceLinkTable,
                StockSourceLink::SOURCE_ID,
                $sourceTable,
                SourceInterface::SOURCE_ID
            ),
            StockSourceLink::SOURCE_ID,
            $sourceTable,
            SourceInterface::SOURCE_ID,
            AdapterInterface::FK_ACTION_CASCADE
        )->addIndex(
            $setup->getIdxName(
                $stockSourceLinkTable,
                [
                    StockSourceLink::STOCK_ID,
                    StockSourceLink::SOURCE_ID,
                ],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [
                StockSourceLink::STOCK_ID,
                StockSourceLink::SOURCE_ID,
            ],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
    }
}
