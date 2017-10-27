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
 * This resource model is responsible for retrieving Stock items by sales channel type and code.
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
     * Returns the linked stock id by given a sales channel type and code.
     *
     * @param string $type
     * @param string $code
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return int
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
