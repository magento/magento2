<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Stock Item Dimension
 */
class DataProvider
{

    const TABLE_NAME_SOURCE_ITEM = 'inventory_source_item';
    const TABLE_NAME_STOCK_SOURCE_LINK = 'inventory_source_stock_link';

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
     *
     * @param array $ids
     * @return \ArrayIterator
     */
    public function fetchDocuments($stockId, array $sourceIds)
    {

        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection();

        $columns = ['sku', 'SUM(quantity) as quantity', 'status'];
        $select = $connection
            ->select()->from(['main' => $this->getSourceItemTableName($connection)], $columns)
            ->joinLeft(
                ['link_table' => $this->getLinkTableName($connection)],
                'main.source_id = link_table.source_id',
                ['stock_id' => 'stock_id']
            )->where(StockInterface::STOCK_ID . '= ?', $stockId);

        if (count($sourceIds) !== 0) {
            $select->where('link_table.source_id=?', $sourceIds);
        }

        $select->group(['sku', 'status']);
        return new \ArrayIterator($connection->fetchAll($select));
    }


    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return string
     */
    private function getSourceItemTableName($connection)
    {
        return $connection->getTableName(self::TABLE_NAME_SOURCE_ITEM);
    }

    /**
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return string
     */
    private function getLinkTableName($connection)
    {
        return $connection->getTableName(self::TABLE_NAME_STOCK_SOURCE_LINK);
    }
}
