<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\DataSavior;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Db\Select;

/**
 * Yields data from database by select objects
 */
class SelectGenerator
{
    /**
     * @var int
     */
    private $batchSize = 30000;

    /**
     * @var int
     */
    private $baseBatchSize;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * TableDump constructor.
     * @param ResourceConnection $resourceConnection
     * @param int $baseBatchSize
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        $baseBatchSize = 30000
    ) {
        $this->baseBatchSize = $baseBatchSize;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * It retrieves data by batches
     *
     * Select generator do not know what data he will fetch, so you need to pass builded Select statement in it
     *
     * @param Select $select
     * @param string $connectionName
     * @return \Generator
     */
    public function generator(Select $select, $connectionName)
    {
        $page = 0;
        $select->limit($this->batchSize, $page * $this->batchSize);
        $adapter = $this->resourceConnection->getConnection($connectionName);
        $data = $adapter->fetchAll($select);
        yield $data;

        while (count($data)) {
            ++$page;
            $select->limit($this->batchSize, $page * $this->batchSize + 1);
            $data = $adapter->fetchAll($select);
            yield $data;
        }
    }
}
