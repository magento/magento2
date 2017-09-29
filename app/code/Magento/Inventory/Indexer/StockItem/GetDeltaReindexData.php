<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Returns all assigned stock ids by given sources ids
 */
class GetDeltaReindexData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Returns all assigned stock ids by given sources item ids.
     *
     * @param int[] $sourceItemIds
     * @return int[] List of stock id to sku1,sku2 assignment
     */
    public function execute(array $sourceItemIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $sourceStockLinkTable = $connection->getTableName(StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK);
        $sourceItemTable = $connection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        $select = $connection
            ->select()
            ->from(
                ['source_item' => $sourceItemTable],
                [
                    SourceItemInterface::SKU => sprintf("GROUP_CONCAT(DISTINCT %s SEPARATOR ',')",
                        'source_item.' . SourceItemInterface::SKU
                    ),
                    'source_item_id'
                ]
            )->joinInner(['stock_source_link' => $sourceStockLinkTable],
                'source_item.' . SourceItemInterface::SOURCE_ID . ' = stock_source_link.' . StockSourceLink::SOURCE_ID,
                [StockSourceLink::STOCK_ID]
            )
            ->where('source_item.source_item_id IN (?)', $sourceItemIds)
            ->group(['stock_source_link.' . StockSourceLink::STOCK_ID]);

        return $connection->fetchAll($select);
    }
}
