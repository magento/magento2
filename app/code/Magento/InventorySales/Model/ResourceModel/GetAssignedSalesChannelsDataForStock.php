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
 * Provides linked sales channels by given stock id
 */
class GetAssignedSalesChannelsDataForStock
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
     * Given a stock id, return array of sales channels assigned to it
     *
     * @param int $stockId
     * @return array
     */
    public function execute(int $stockId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(CreateSalesChannelTable::TABLE_NAME_SALES_CHANNEL);

        $select = $connection->select()
            ->from($tableName)
            ->where(CreateSalesChannelTable::STOCK_ID . ' = ?', $stockId);

        return $connection->fetchAll($select);
    }
}
