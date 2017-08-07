<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

/**
 * Encapsulate logic that performs batch insert into table
 * @since 2.2.0
 */
class BatchInsert
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.2.0
     */
    private $dbConnection;

    /**
     * @var string
     * @since 2.2.0
     */
    private $insertIntoTable;

    /**
     * @var int
     * @since 2.2.0
     */
    private $batchSize;

    /**
     * @var array
     * @since 2.2.0
     */
    private $dataStorage;

    /**
     * @var int
     * @since 2.2.0
     */
    private $currentStorageIndex = 0;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param string $insertIntoTable
     * @param int $batchSize
     * @since 2.2.0
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
     * Save data to $dataStorage and automatically flush it to DB
     * when storage size becomes equal to $batchSize
     *
     * @param array $dataToInsert
     * @return void
     * @since 2.2.0
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
     * @since 2.2.0
     */
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
     * @since 2.2.0
     */
    private function getDbConnection()
    {
        if ($this->dbConnection === null) {
            $this->dbConnection = $this->resourceConnection->getConnection();
        }

        return $this->dbConnection;
    }
}
