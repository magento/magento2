<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\App\ResourceConnection;

class Checker
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
     * @param int $stockId
     * @param string $sku
     *
     * @return float qty
     */
    public function execute($stockId, $sku)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_stock_item_stock_' . $stockId);

        $result = 0;
        // can make select to non exitsing table count must be 0.
        if ($connection->isTableExists($tableName) === true) {
            $select = $connection
                ->select()->from($tableName, ['quantity'])
                ->where('sku=?', $sku);

            $result = $connection->fetchOne($select);
        }
        return $result;
    }
}
