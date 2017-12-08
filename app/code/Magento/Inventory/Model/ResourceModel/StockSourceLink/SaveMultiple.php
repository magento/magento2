<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $sourceIds
     * @param int $stockId
     * @return void
     */
    public function execute(array $sourceIds, int $stockId)
    {
        if (!count($sourceIds)) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(
            StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK
        );

        $columns = [
            StockSourceLink::SOURCE_ID,
            StockSourceLink::STOCK_ID,
        ];

        $data = [];
        foreach ($sourceIds as $sourceId) {
            $data[] = [$sourceId, $stockId];
        }
        if ($data) {
            $connection->insertArray($tableName, $columns, $data);
        }
    }
}
