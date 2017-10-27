<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventorySales\Model\ResourceModel\SalesChannel as SalesChannelResourceModel;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

class CreateSalesChannelTable
{
    /**
     * Constant for key of data array. It is defined here because it's not part of the interface.
     */
    const STOCK_ID = 'stock_id';

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $salesChannelTable = $this->createSalesChannelTable($setup);
        $setup->getConnection()->createTable($salesChannelTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createSalesChannelTable(SchemaSetupInterface $setup): Table
    {
        $salesChannelTable = $setup->getTable(SalesChannelResourceModel::TABLE_NAME_SALES_CHANNEL);

        return $setup->getConnection()->newTable(
            $salesChannelTable
        )->setComment(
            'Inventory Sales Channel Table'
        )->addColumn(
            SalesChannelInterface::ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Sales Channel ID'
        )->addColumn(
            SalesChannelInterface::TYPE,
            Table::TYPE_TEXT,
            64,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Sales Channel Type'
        )->addColumn(
            SalesChannelInterface::CODE,
            Table::TYPE_TEXT,
            64,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Sales Channel Code'
        )->addColumn(
            self::STOCK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_NULLABLE => false,
                Table::OPTION_UNSIGNED => true,
            ],
            'Stock Id'
        )->addForeignKey($setup->getFkName('inventory_stock_sales_channel', 'stock_id', 'inventory_stock', 'stock_id'),
            'stock_id',
            $setup->getTable('inventory_stock'),
            'stock_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_RESTRICT);
    }
}
