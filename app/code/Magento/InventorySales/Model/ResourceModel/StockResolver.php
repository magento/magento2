<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * The resource model responsible for retrieving StockItem Quantity.
 * Used by Service Contracts that are agnostic to the Data Access Layer.
 */
class StockResolver
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
     * Given a product sku and a stock id, return stock item quantity.
     *
     * @param string $type
     * @param string $code
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(string $type, string $code): int
    {
        $connection = $this->resource->getConnection();

        $tableName = $this->resource->getTableName(
            SalesChannel::TABLE_NAME_SALES_CHANNEL
        );

        $select = $connection->select()
            ->from($tableName, 'stock_id')
            ->where(SalesChannelInterface::TYPE . ' = ?', $type)
            ->where(SalesChannelInterface::CODE . ' = ?', $code);

        $stockId = $connection->fetchOne($select);
        if (false === $stockId) {
            throw new NoSuchEntityException(__('No linked stock found!'));
        }

        return (int)$stockId;
    }
}
