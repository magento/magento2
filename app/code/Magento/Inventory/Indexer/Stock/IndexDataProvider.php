<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\Stock;

use ArrayIterator;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\SelectBuilder;

/**
 * Index data provider
 */
class IndexDataProvider
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
     * Returns all data for the index.
     *
     * @param int $stockId
     * @return ArrayIterator
     */
    public function getData(int $stockId): ArrayIterator
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $this->selectBuilder->execute($stockId);

        return new ArrayIterator($connection->fetchAll($select));
    }
}