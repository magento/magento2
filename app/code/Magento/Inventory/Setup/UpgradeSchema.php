<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\SourceStockLinkInterface;

/**
 * Class for integration tables schema upgrades
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Constant for table names of the model \Magento\Inventory\Model\Source
     */
    const TABLE_NAME_STOCK = 'inventory_stock';

    /**
     * Constant for table name of \Magento\Inventory\Model\SourceStockLink
     */
    const TABLE_NAME_SOURCE_STOCK_LINK = 'inventory_source_stock_link';
    /**
     * Option keys for column options
     */
    const OPTION_IDENTITY = 'identity';
    const OPTION_UNSIGNED = 'unsigned';
    const OPTION_NULLABLE = 'nullable';
    const OPTION_PRIMARY = 'primary';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '>')) {
            $stockTable = $this->createStockTable($setup);
            $setup->getConnection()->createTable($stockTable);
            $stockLinkTable = $this->createSourceStockLinkTable($setup);
            $setup->getConnection()->createTable($stockLinkTable);
        }

        $setup->endSetup();
    }

    private function createStockTable($setup) {
        /**
         * Create table 'inventory_stock'
         */
        $sourceTable = $setup->getTable(UpgradeSchema::TABLE_NAME_STOCK);

        return $setup->getConnection()->newTable(
            $sourceTable
        )->setComment(
            'Inventory Stock Table'
        )->addColumn(
            StockInterface::STOCK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                UpgradeSchema::OPTION_IDENTITY => true,
                UpgradeSchema::OPTION_UNSIGNED => true,
                UpgradeSchema::OPTION_NULLABLE => false,
                UpgradeSchema::OPTION_PRIMARY => true,
            ],
            'Stock ID'
        )->addColumn(
            StockInterface::NAME,
            Table::TYPE_TEXT,
            255,
            [
                UpgradeSchema::OPTION_NULLABLE => false,
            ],
            'Stock Name'
        );
    }

    private function createSourceStockLinkTable($setup) {
        /**
         * Create table 'inventory_source_stock_link'
         */
        $sourceTable = $setup->getTable(UpgradeSchema::TABLE_NAME_SOURCE_STOCK_LINK);

        return $setup->getConnection()->newTable(
            $sourceTable
        )->setComment(
            'Inventory Source Stock Link Table'
        )->addColumn(
            SourceStockLinkInterface::LINK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                UpgradeSchema::OPTION_IDENTITY => true,
                UpgradeSchema::OPTION_UNSIGNED => true,
                UpgradeSchema::OPTION_NULLABLE => false,
                UpgradeSchema::OPTION_PRIMARY => true,
            ],
            'Link ID'
        )->addColumn(
            SourceStockLinkInterface::STOCK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                UpgradeSchema::OPTION_NULLABLE => false,
            ],
            'Stock Id'
        )->addColumn(
            SourceStockLinkInterface::SOURCE_ID,
            Table::TYPE_INTEGER,
            null,
            [
                UpgradeSchema::OPTION_NULLABLE => false,
            ],
            'Source Id'
        );
    }
}
