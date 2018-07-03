<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * This resource model is responsible for retrieving Stock items by sales channel type and code
 * Used by Service Contracts that are agnostic to the Data Access Layer
 */
class StockIdResolver
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
     * Returns the linked stock id by given a sales channel type and code
     *
     * @param string $type
     * @param string $code
     * @return int|null
     */
    public function resolve(string $type, string $code)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_stock_sales_channel');

        $select = $connection->select()
            ->from($tableName, 'stock_id')
            ->where(SalesChannelInterface::TYPE . ' = ?', $type)
            ->where(SalesChannelInterface::CODE . ' = ?', $code);

        $stockId = $connection->fetchOne($select);
        return false === $stockId ? null : (int)$stockId;
    }
}
