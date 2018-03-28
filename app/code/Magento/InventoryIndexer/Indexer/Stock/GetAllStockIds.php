<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Indexer\Stock;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\Stock as StockResourceModel;
use Magento\Inventory\Model\StockSourceLink;

/**
 * Returns all stock ids.
 */
class GetAllStockIds
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
     * @return int[]
     */
    public function execute(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $stockTable = $this->resourceConnection->getTableName(StockResourceModel::TABLE_NAME_STOCK);

        $select = $connection->select()->from($stockTable, StockSourceLink::STOCK_ID);

        $stockIds = $connection->fetchCol($select);
        $stockIds = array_map('intval', $stockIds);

        return $stockIds;
    }
}
