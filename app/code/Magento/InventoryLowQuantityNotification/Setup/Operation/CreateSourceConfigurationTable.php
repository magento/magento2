<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;

class CreateSourceConfigurationTable
{
    /**
     * Source item configuration table name
     */
    const TABLE_NAME_SOURCE_ITEM_CONFIGURATION = 'inventory_low_stock_notification_configuration';

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $sourceItemConfigurationTable = $setup->getConnection()->newTable(
            $setup->getTable(self::TABLE_NAME_SOURCE_ITEM_CONFIGURATION)
        )->setComment(
            'Inventory source Item Configuration Table'
        );

        $sourceItemConfigurationTable = $this->addBaseFields($sourceItemConfigurationTable);
        $sourceItemConfigurationTable = $this->addForeignKey($sourceItemConfigurationTable, $setup);

        $setup->getConnection()->createTable($sourceItemConfigurationTable);
    }

    /**
     * Add columns to table
     *
     * @param Table $sourceItemConfigurationTable
     * @return Table
     */
    private function addBaseFields(Table $sourceItemConfigurationTable): Table
    {
        return $sourceItemConfigurationTable->addColumn(
            SourceItemConfigurationInterface::SOURCE_CODE,
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
            [SourceItemConfigurationInterface::SOURCE_CODE, SourceItemInterface::SKU],
            ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
        );
    }

    /**
     * Add foreign key to table
     *
     * @param Table $sourceItemConfigurationTable
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function addForeignKey(
        Table $sourceItemConfigurationTable,
        SchemaSetupInterface $setup
    ): Table {
        $sourceItemTable = $setup->getTable(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);

        return $sourceItemConfigurationTable->addForeignKey(
            $setup->getFkName(
                $sourceItemConfigurationTable->getName(),
                SourceItemConfigurationInterface::SOURCE_CODE,
                $sourceItemTable,
                SourceItemInterface::SOURCE_CODE
            ),
            SourceItemConfigurationInterface::SOURCE_CODE,
            $sourceItemTable,
            SourceItemInterface::SOURCE_CODE,
            AdapterInterface::FK_ACTION_CASCADE
        );
    }
}
