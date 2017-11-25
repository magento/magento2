<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\SourceItem;

use ArrayIterator;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Inventory\Indexer\SelectBuilder;

/**
 * Index by sku list data provider
 */
class IndexDataBySkuListProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SelectBuilder $selectBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SelectBuilder $selectBuilder
    ){
        $this->resourceConnection = $resourceConnection;
        $this->selectBuilder = $selectBuilder;
    }

    /**
     * Returns all data for the index by SKU List condition.
     *
     * @param int $stockId
     * @param array $skuList
     * @return ArrayIterator
     */
    public function getDataBySkuList(int $stockId, array $skuList): ArrayIterator
    {
        $connection = $this->resourceConnection->getConnection();
        $conditions = [];
        if (count($skuList)) {
            $conditions[] = [
                'condition' => 'source_item.' . SourceItemInterface::SKU . ' IN (?)',
                'value' => $skuList
            ];
        }
        $select = $this->selectBuilder->execute($stockId, $conditions);

        return new ArrayIterator($connection->fetchAll($select));
    }
}