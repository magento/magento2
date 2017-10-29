<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySales\Setup\Operation\CreateSalesChannelTable;

/**
 * Provides linked sales channels by given stock id.
 */
class GetAssignedSalesChannelsDataForStock
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Given a stock id, return array of sales channels assigned to it.
     *
     * @param int $stockId
     * @return array
     */
    public function execute(int $stockId): array
    {
        $connection = $this->resource->getConnection();

        $tableName = $this->resource->getTableName(
            CreateSalesChannelTable::TABLE_NAME_SALES_CHANNEL
        );

        $select = $connection->select()
            ->from($tableName)
            ->where('stock_id = ?', $stockId);

        return $connection->fetchAll($select);
    }
}
