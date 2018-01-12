<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySalesApi\Api\Data\GetSalesChannelToStockDataInterface;
use Magento\InventorySales\Setup\Operation\CreateSalesChannelTable;

/**
 * @inheritdoc
 */
class GetSalesChannelToStockData implements GetSalesChannelToStockDataInterface
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
     * @return array | null
     */
    public function execute(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(CreateSalesChannelTable::TABLE_NAME_SALES_CHANNEL);

        $select = $connection->select()
            ->from($tableName, [CreateSalesChannelTable::STOCK_ID]);

        $result = $connection->fetchAll($select);

        if (count($result) === 0) {
            return null;
        }
        return $result;
    }
}
