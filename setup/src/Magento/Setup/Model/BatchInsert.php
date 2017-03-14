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

    /**
     * @var int
     */
    private $currentStorageIndex = 0;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        $insertIntoTable,
        $batchSize
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->insertIntoTable = $insertIntoTable;
        $this->batchSize = $batchSize;

        $this->dataStorage = new \SplFixedArray($batchSize);
    }

    public function insert(array $dataToInsert)
    {
        $this->dataStorage[$this->currentStorageIndex] = $dataToInsert;
        $this->currentStorageIndex++;

        if ($this->currentStorageIndex >= $this->batchSize) {
            $this->flush();
        }
    }

    public function flush()
    {
        if ($this->currentStorageIndex > 0) {
            if ($this->currentStorageIndex < $this->batchSize) {
                $this->dataStorage->setSize($this->currentStorageIndex);
            }

            $this->getDbConnection()
                ->insertArray(
                    $this->insertIntoTable,
                    array_keys(reset($this->dataStorage)),
                    $this->dataStorage->toArray()
                );

            $this->dataStorage = new \SplFixedArray($this->batchSize);
            $this->currentStorageIndex = 0;
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
