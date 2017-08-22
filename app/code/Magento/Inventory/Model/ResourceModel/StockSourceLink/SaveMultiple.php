<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel\StockSourceLink;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;

/**
 * Implementation of StockSourceLink save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class SaveMultiple
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * SourceCarrierLinkManagement constructor
     *
     * @param ResourceConnection $connection
     */
    public function __construct(
        ResourceConnection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @param int $stockId
     * @param array $sourceIds
     * @return void
     */
    public function execute(array $sourceIds, $stockId)
    {
        $connection = $this->connection->getConnection();
        $tableName = $connection->getTableName(StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK);

        $columns = [
            StockSourceLink::SOURCE_ID,
            StockSourceLink::STOCK_ID,
        ];

        $data = [];
        foreach ($sourceIds as $sourceId) {
            $data[] = [$sourceId, $stockId];
        }
        $connection->insertArray($tableName, $columns, $data);
    }
}
