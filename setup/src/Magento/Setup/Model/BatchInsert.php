<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

class BatchInsert
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $dbConnection;

    /**
     * @var string
     */
    private $insertIntoTable;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var array
     */
    private $dataStorage;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        $insertIntoTable,
        $batchSize
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->insertIntoTable = $insertIntoTable;
        $this->batchSize = $batchSize;
    }

    public function insert(array $dataToInsert)
    {
        $this->dataStorage[] = $dataToInsert;

        if (count($this->dataStorage) >= $this->batchSize) {
            $this->flush();
        }
    }

    public function flush()
    {
        if (count($this->dataStorage) > 0) {
            $this->getDbConnection()
                ->insertArray(
                    $this->insertIntoTable,
                    array_keys(reset($this->dataStorage)),
                    $this->dataStorage
                );

            $this->dataStorage = [];
        }
    }

    /**
     * Retrieve current connection to DB
     *
     * Method is required to eliminate multiple calls to ResourceConnection class
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getDbConnection()
    {
        if ($this->dbConnection === null) {
            $this->dbConnection = $this->resourceConnection->getConnection();
        }

        return $this->dbConnection;
    }
}
