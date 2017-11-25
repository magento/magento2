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
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;

class CreateSourceConfigurationTable
{
    /**C
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $notifyQtyTable = $setup->getConnection()->newTable(
            $setup->getTable(SourceItemConfiguration::TABLE_NAME_SOURCE_ITEM_CONFIGURATION)
        )->setComment(
            'Inventory Notification Table'
        );

        $sourceItemTable = $setup->getTable(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);

        $notifyQtyTable = $this->addBaseFields($notifyQtyTable);
        $notifyQtyTable = $this->addForeignKey($notifyQtyTable, $sourceItemTable, $setup);

        $setup->getConnection()->createTable($notifyQtyTable);
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
            SourceItemConfigurationInterface::SOURCE_ITEM_ID,
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
        );
    }

    /**
     * Add foreign key to table.
     *
     * @param Table $notifyQtyTable
     * @param string $sourceItemTable
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function addForeignKey(Table $notifyQtyTable, string $sourceItemTable, SchemaSetupInterface $setup): Table
    {
        return $notifyQtyTable->addForeignKey(
            $setup->getFkName(
                $notifyQtyTable->getName(),
                SourceItemConfigurationInterface::SOURCE_ITEM_ID,
                $sourceItemTable,
                SourceItem::ID_FIELD_NAME
            ),
            SourceItemConfigurationInterface::SOURCE_ITEM_ID,
            $sourceItemTable,
            SourceItem::ID_FIELD_NAME,
            AdapterInterface::FK_ACTION_CASCADE
        );
    }
}
