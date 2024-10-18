<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

/**
 * Encapsulate logic that performs batch insert into table
 */
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
     * @var \SplFixedArray
     */
    private $dataStorage;

    /**
     * @var int
     */
    private $currentStorageIndex = 0;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param string $insertIntoTable
     * @param int $batchSize
     */
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

    /**
     * Save data to $dataStorage and automatically flush it to DB when storage size becomes equal to $batchSize
     *
     * @param array $dataToInsert
     * @return void
     */
    public function insert(array $dataToInsert)
    {
        $this->dataStorage[$this->currentStorageIndex] = $dataToInsert;
        $this->currentStorageIndex++;

        if ($this->currentStorageIndex >= $this->batchSize) {
            $this->flush();
        }
    }

    /**
     * Insert all data form $dataStorage to DB and clear $dataStorage
     *
     * @return void
     */
    public function flush()
    {
        if ($this->currentStorageIndex > 0) {
            if ($this->currentStorageIndex < $this->batchSize) {
                $this->dataStorage->setSize($this->currentStorageIndex);
            }

            if (method_exists($this->dataStorage, 'getIterator')) {
                // PHP > 8.0
                $this->dataStorage->getIterator()->rewind();
                $columnsToInsert = array_keys($this->dataStorage->getIterator()->current());
            } else {
                // PHP 7.4. compatibility
                $this->dataStorage->rewind();
                $columnsToInsert = array_keys($this->dataStorage->current());
            }
            $this->getDbConnection()
                ->insertArray(
                    $this->insertIntoTable,
                    $columnsToInsert,
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
