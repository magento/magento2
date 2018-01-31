<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryIndexer\Model\StockCondition\GetStockConditionInterface;

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
     * @var GetStockConditionInterface
     */
    private $getStockCondition;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetStockConditionInterface $getStockCondition
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetStockConditionInterface $getStockCondition
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getStockCondition = $getStockCondition;
    }

    /**
     * Prepare select.
     *
     * @param int $stockId
     * @return Select
     */
    public function execute($stockId): Select
    {
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

        $select->from(
            ['source_item' => $sourceItemTable],
            [
                SourceItemInterface::SKU,
                SourceItemInterface::QUANTITY => 'SUM(' . $quantityExpression . ')',
                IndexStructure::IS_SALABLE => 'MAX(' . $this->getStockCondition->execute() . ')'
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
