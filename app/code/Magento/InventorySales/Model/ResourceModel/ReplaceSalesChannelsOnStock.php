<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\InventorySales\Setup\Operation\CreateSalesChannelTable;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * Implementation of Link Replacement between Stock and Sales Channels - delete and save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class ReplaceSalesChannelsOnStock
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
    )
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Replace existing or non existing Sales Channels for Stock
     *
     * @param SalesChannelInterface[] $salesChannels
     * @param int $stockId
     */
    public function execute(array $salesChannels, int $stockId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(CreateSalesChannelTable::TABLE_NAME_SALES_CHANNEL);
        $connection->delete($tableName, [CreateSalesChannelTable::STOCK_ID . ' = ?' => $stockId]);
        $salesChannelsToInsert = [];
        foreach ($salesChannels as $salesChannel) {
            $salesChannelsToInsert[] = [
                SalesChannelInterface::TYPE => $salesChannel->getType(),
                SalesChannelInterface::CODE => $salesChannel->getCode(),
                CreateSalesChannelTable::STOCK_ID => $stockId,
            ];
        }
        $connection->insertMultiple($tableName, $salesChannelsToInsert);
    }
}
