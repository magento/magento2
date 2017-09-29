<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;

/**
 * Returns all assigned stock ids by given sources ids
 */
class GetFullReindexData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Returns all assigned stock ids
     *
     * @return int[] List of stock ids
     */
    public function execute(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $connection->getTableName(StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK),
            [StockSourceLink::STOCK_ID]
        );

        $select->group(StockSourceLink::STOCK_ID);
        return $connection->fetchCol($select);
    }
}
