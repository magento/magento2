<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\InventoryApi\Api\Data\StockInterface;

class CreateStockTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $stockTable = $this->createStockTable($setup);

        $setup->getConnection()->createTable($stockTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createStockTable(SchemaSetupInterface $setup): Table
    {
        $stockTable = $setup->getTable(StockResourceModel::TABLE_NAME_STOCK);

        return $setup->getConnection()->newTable(
            $stockTable
        )->setComment(
            'Inventory Stock Table'
        )->addColumn(
            StockInterface::STOCK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Stock ID'
        )->addColumn(
            StockInterface::NAME,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Stock Name'
        );
    }
}
