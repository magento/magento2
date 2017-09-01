<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Returns all assigned stock ids by given sources ids
 */
class GetAssignedStockIds
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
     * Returns all assigned stock ids by given sources ids
     *
     * @param array $sourceIds
     * @return int[] List of stock ids
     */
    public function execute(array $sourceIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $connection->getTableName(StockSourceLink::TABLE_NAME_STOCK_SOURCE_LINK),
            [StockInterface::STOCK_ID]
        );

        if (count($sourceIds)) {
            $select->where(SourceInterface::SOURCE_ID . ' = ?', $sourceIds);
        }
        $select->group(StockInterface::STOCK_ID);
        return $connection->fetchCol($select);
    }
}
