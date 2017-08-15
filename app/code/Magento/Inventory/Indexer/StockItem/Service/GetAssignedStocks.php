<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem\Service;


use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\StockInterface;

class GetAssignedStocks implements GetAssignedStocksInterface
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $sourceIds)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $connection->getTableName('inventory_source_stock_link'),
            [StockInterface::STOCK_ID]
        );

        if (count($sourceIds) !== 0) {
            $select->where(SourceInterface::SOURCE_ID . ' (?)', $sourceIds);
        }
        $select->group(StockInterface::STOCK_ID);

        return $connection->fetchAll($select);
    }
}
