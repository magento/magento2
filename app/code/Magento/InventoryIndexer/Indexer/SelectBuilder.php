<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Indexer;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Select builder data provider
 */
class SelectBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Configuration $configuration
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Configuration $configuration
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configuration = $configuration;
    }

    /**
     * Prepare select.
     *
     * @param int $stockId
     * @return Select
     */
    public function execute($stockId): Select
    {
        $globalManageStock = $this->configuration->getManageStock();
        $globalMinQty = $this->configuration->getMinQty();

        $connection = $this->resourceConnection->getConnection();
        $sourceTable = $this->resourceConnection->getTableName(SourceResourceModel::TABLE_NAME_SOURCE);
        $sourceItemTable = $this->resourceConnection->getTableName(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);
        $sourceStockLinkTable = $this->resourceConnection->getTableName(
            StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK
        );

        // find all enabled sources
        $select = $connection->select()
            ->from($sourceTable, [SourceInterface::SOURCE_CODE])
            ->where(SourceInterface::ENABLED . ' = ?', 1);
        $sourceCodes = $connection->fetchCol($select);

        if (0 === count($sourceCodes)) {
            return $select;
        }

        $select = $connection->select();
        $quantityExpression = (string)$select->getConnection()->getCheckSql(
            'source_item.' . SourceItemInterface::STATUS . ' = ' . SourceItemInterface::STATUS_OUT_OF_STOCK,
            0,
            SourceItemInterface::QUANTITY
        );

        $isSalableString = sprintf(
            '(((legacy_stock_item.use_config_manage_stock = 1 AND 0 = %1$d)'
            . ' OR (legacy_stock_item.use_config_manage_stock = 0 AND legacy_stock_item.manage_stock = 0))'
            . ' OR ((legacy_stock_item.use_config_min_qty = 1 AND ' . $quantityExpression . ' > %2$d)'
            . ' OR (legacy_stock_item.use_config_min_qty = 0 AND'
            . ' ' . $quantityExpression . ' > legacy_stock_item.min_qty))'
            //todo https://github.com/magento-engcom/msi/issues/479
            . ' OR product_entity.type_id = \'bundle\')',
            $globalManageStock,
            $globalMinQty
        );

        $isSalableExpression = $select->getConnection()->getCheckSql($isSalableString, 1, 0);

        $select->from(
            ['source_item' => $sourceItemTable],
            [
                SourceItemInterface::SKU,
                SourceItemInterface::QUANTITY => 'SUM(' . $quantityExpression . ')',
                IndexStructure::IS_SALABLE => 'MAX(' . $isSalableExpression . ')'
            ]
        )->joinLeft(
            ['stock_source_link' => $sourceStockLinkTable],
            sprintf(
                'source_item.%s = stock_source_link.%s',
                SourceItemInterface::SOURCE_CODE,
                StockSourceLink::SOURCE_CODE
            ),
            []
        )->joinInner(
            ['product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'product_entity.sku = source_item.sku',
            []
        )->joinInner(
            ['legacy_stock_item' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
            'product_entity.entity_id = legacy_stock_item.product_id',
            []
        )
            ->where('stock_source_link.' . StockSourceLink::STOCK_ID . ' = ?', $stockId)
            ->where('stock_source_link.' . StockSourceLink::SOURCE_CODE . ' IN (?)', $sourceCodes)
            ->group([SourceItemInterface::SKU]);

        return $select;
    }
}
