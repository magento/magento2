<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

class CreateSourceConfigurationTable
{
    const TABLE_NAME_SOURCE_ITEM_CONFIGURATION = 'inventory_source_item_configuration';

    /**C
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $sourceItemConfigurationTable = $setup->getConnection()->newTable(
            $setup->getTable(self::TABLE_NAME_SOURCE_ITEM_CONFIGURATION)
        )->setComment(
            'Inventory Configuration Table'
        );

        $sourceItemTable = $setup->getTable(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);

        $sourceItemConfigurationTable = $this->addBaseFields($sourceItemConfigurationTable);
        $sourceItemConfigurationTable = $this->addForeignKey($sourceItemConfigurationTable, $sourceItemTable, $setup);

        $setup->getConnection()->createTable($sourceItemConfigurationTable);
    }

    /**
     * Add columns to table.
     *
     * @param Table $notifyQtyTable
     * @return Table
     */
    private function addBaseFields(Table $notifyQtyTable): Table
    {
        return $notifyQtyTable->addColumn(
            SourceItemConfigurationInterface::SOURCE_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ],
            'Source ID'
        )->addColumn(
            SourceItemInterface::SKU,
            Table::TYPE_TEXT,
            64,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Sku'
        )->addColumn(
            SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY,
            Table::TYPE_DECIMAL,
            null,
            [
                Table::OPTION_UNSIGNED => false,
                Table::OPTION_NULLABLE => true,
                Table::OPTION_DEFAULT => null,
                Table::OPTION_PRECISION => 12,
                Table::OPTION_SCALE => 4,
            ],
            'Notify Quantity'
        )->addIndex(
            'idx_primary',
            [SourceItemConfigurationInterface::SOURCE_ID, SourceItemInterface::SKU],
            ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
        );
    }

    /**
     * Add foreign key to table.
     *
     * @param Table $sourceItemConfigurationTable
     * @param string $inventorySourceTable
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function addForeignKey(
        Table $sourceItemConfigurationTable,
        string $inventorySourceTable,
        SchemaSetupInterface $setup
    ): Table {
        return $sourceItemConfigurationTable->addForeignKey(
            $setup->getFkName(
                $sourceItemConfigurationTable->getName(),
                SourceItemConfigurationInterface::SOURCE_ID,
                $inventorySourceTable,
                SourceInterface::SOURCE_ID
            ),
            SourceItemConfigurationInterface::SOURCE_ID,
            $inventorySourceTable,
            SourceInterface::SOURCE_ID,
            AdapterInterface::FK_ACTION_CASCADE
        );
    }
}
