<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Indexer\SourceItem;

use ArrayIterator;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryIndexer\Indexer\SelectBuilder;

/**
 * Returns all data for the index by SKU List condition
 */
class IndexDataBySkuListProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SelectBuilder
     */
    private $selectBuilder;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SelectBuilder $selectBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SelectBuilder $selectBuilder
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->selectBuilder = $selectBuilder;
    }

    /**
     * @param int $stockId
     * @param array $skuList
     * @return ArrayIterator
     */
    public function execute(int $stockId, array $skuList): ArrayIterator
    {
        $select = $this->selectBuilder->execute($stockId);

        if (count($skuList)) {
            $select->where('source_item.' . SourceItemInterface::SKU . ' IN (?)', $skuList);
        }

        $connection = $this->resourceConnection->getConnection();
        return new ArrayIterator($connection->fetchAll($select));
    }
}
