<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySales\Model\SalesChannelFactory;

/**
 * Provides linked sales channels by given stock id.
 */
class SalesChannelsProvider
{
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var SalesChannelFactory
     */
    private $salesChannelFactory;

    /**
     * @param ResourceConnection $resource
     * @param SalesChannelFactory $salesChannelFactory
     */
    public function __construct(
        ResourceConnection $resource,
        SalesChannelFactory $salesChannelFactory
    ) {
        $this->resource = $resource;
        $this->salesChannelFactory = $salesChannelFactory;
    }

    /**
     * Given a stock id, return array of sales channels assigned to it.
     *
     * @param int $stockId
     * @return array
     */
    public function resolve(int $stockId): array
    {
        $connection = $this->resource->getConnection();

        $tableName = $this->resource->getTableName(
            SalesChannel::TABLE_NAME_SALES_CHANNEL
        );

        $select = $connection->select()
            ->from($tableName)
            ->where('stock_id' . ' = ?', $stockId);

        return $connection->fetchAssoc($select);
    }
}
