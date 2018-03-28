<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Indexer\Stock;

use ArrayIterator;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfigurableProductIndexer\Indexer\SelectBuilder;

/**
 * Returns all data for the index by stock id condition
 */
class IndexDataByStockIdProvider
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
     * @return ArrayIterator
     */
    public function execute(int $stockId): ArrayIterator
    {
        $select = $this->selectBuilder->execute($stockId);

        $connection = $this->resourceConnection->getConnection();
        return new ArrayIterator($connection->fetchAll($select));
    }
}
