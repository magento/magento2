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
use Magento\Inventory\Model\ResourceModel\SourceCarrierLink;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

class CreateSourceCarrierLinkTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $sourceCarrierLinkTable = $this->createSourceCarrierLinkTable($setup);

        $setup->getConnection()->createTable($sourceCarrierLinkTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    private function createSourceCarrierLinkTable(SchemaSetupInterface $setup): Table
    {
        $sourceCarrierLinkTable = $setup->getTable(SourceCarrierLink::TABLE_NAME_SOURCE_CARRIER_LINK);
        $sourceTable = $setup->getTable(SourceResourceModel::TABLE_NAME_SOURCE);

        return $setup->getConnection()->newTable(
            $sourceCarrierLinkTable
        )->setComment(
            'Inventory Source Carrier Link Table'
        )->addColumn(
            SourceCarrierLink::ID_FIELD_NAME,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Source Carrier Link ID'
        )->addColumn(
            SourceInterface::CODE,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Source Code'
        )->addColumn(
            SourceCarrierLinkInterface::CARRIER_CODE,
            Table::TYPE_TEXT,
            255,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Carrier Code'
        )->addColumn(
            'position',
            Table::TYPE_SMALLINT,
            null,
            [
                Table::OPTION_NULLABLE => true,
                Table::OPTION_UNSIGNED => true,
            ],
            'Position'
        )->addForeignKey(
            $setup->getFkName(
                $sourceCarrierLinkTable,
                SourceInterface::CODE,
                $sourceTable,
                SourceInterface::CODE
            ),
            SourceInterface::CODE,
            $sourceTable,
            SourceInterface::CODE,
            AdapterInterface::FK_ACTION_CASCADE
        );
    }
}
