<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySales\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventorySales\Model\ResourceModel\StockChannel as StockChannelResourceModel;
use Magento\InventorySalesApi\Api\Data\StockChannelInterface;

class CreateStockChannelTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $stockChannelTable = $this->createStockChannelTable($setup);

        $setup->getConnection()->createTable($stockChannelTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createStockChannelTable(SchemaSetupInterface $setup): Table
    {
        $stockChannelTable = $setup->getTable(StockChannelResourceModel::TABLE_NAME_STOCK_CHANNEL);

        return $setup->getConnection()->newTable(
            $stockChannelTable
        )->setComment(
            'Inventory Stock Channel Table'
        )->addColumn(
            StockChannelInterface::STOCK_CHANNEL_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Stock Channel ID'
        )->addColumn(
            StockChannelInterface::TYPE,
            Table::TYPE_SMALLINT,
            null,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Stock Channel Type'
        )->addColumn(
            StockChannelInterface::CODE,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Stock Channel Code'
        )->addColumn(
            StockChannelInterface::STOCK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_UNSIGNED => true,
            ],
            'Stock Id'
        )->addForeignKey($setup->getFkName('inventory_stock_channel', 'stock_id', 'inventory_stock', 'stock_id'),
            'stock_id',
            $setup->getTable('inventory_stock'),
            'stock_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_RESTRICT);
    }
}
