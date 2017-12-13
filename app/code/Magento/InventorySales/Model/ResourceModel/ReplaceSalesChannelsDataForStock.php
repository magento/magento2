<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySales\Model\ReplaceSalesChannelsForStockInterface;
use Magento\InventorySales\Setup\Operation\CreateSalesChannelTable;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * Implementation of links replacement between Stock and Sales Channels for specific db layer
 *
 * There is no additional business logic on SPI (Service Provider Interface) level so could use resource model as
 * SPI implementation directly
 */
class ReplaceSalesChannelsDataForStock implements ReplaceSalesChannelsForStockInterface
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
     * @inheritdoc
     */
    public function execute(array $salesChannels, int $stockId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(CreateSalesChannelTable::TABLE_NAME_SALES_CHANNEL);

        $connection->delete($tableName, [CreateSalesChannelTable::STOCK_ID . ' = ?' => $stockId]);

        if (count($salesChannels)) {
            $salesChannelsToInsert = [];
            foreach ($salesChannels as $salesChannel) {
                $salesChannelsToInsert[] = [
                    SalesChannelInterface::TYPE => $salesChannel->getType(),
                    SalesChannelInterface::CODE => $salesChannel->getCode(),
                    CreateSalesChannelTable::STOCK_ID => $stockId,
                ];
            }
            $connection->insertOnDuplicate($tableName, $salesChannelsToInsert);
        }
    }
}
