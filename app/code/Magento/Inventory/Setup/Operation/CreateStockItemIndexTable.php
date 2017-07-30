<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Inventory\Model\Indexer\StockItem;
use Magento\InventoryApi\Api\Data\StockItemInterface;

class CreateStockItemIndexTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $stockTable = $this->createStockItemIndexTable($setup);
        $setup->getConnection()->createTable($stockTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createStockItemIndexTable(SchemaSetupInterface $setup)
    {
        $sourceItemTable = $setup->getTable(StockItem::TABLE_NAME_STOCK_ITEM_INDEX);
        return $setup->getConnection()->newTable(
            $sourceItemTable
        )->setComment(
            'Inventory Stock item Table'
        )->addColumn(
            'stock_item_id',
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Stock Item ID'
        )->addColumn(
            StockItemInterface::STOCK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ],
            'Stock ID'
        )->addColumn(
            StockItemInterface::SKU,
            Table::TYPE_TEXT,
            64,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Sku'
        )->addColumn(
            StockItemInterface::QUANTITY,
            Table::TYPE_DECIMAL,
            null,
            [
                Table::OPTION_UNSIGNED => false,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
            ],
            'Quantity'
        )->addColumn(
            StockItemInterface::STATUS,
            Table::TYPE_SMALLINT,
            null,
            [
                Table::OPTION_NULLABLE => true,
                Table::OPTION_UNSIGNED => true,
            ],
            'Status'
        );
    }
}
