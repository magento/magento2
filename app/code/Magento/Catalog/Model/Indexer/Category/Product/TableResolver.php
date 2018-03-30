<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Category\Product;

use Magento\Framework\Exception\LocalizedException;

class TableResolver
{
    /**
     * Catalog category index table name
     */
    private $mainIndexTable = 'catalog_category_product_index';

    /**
     * Catalog category index store suffix
     */
    private $mainIndexTableStoreSuffix = '_store_';

    /**
     * Catalog tmp category index table name
     */
    private $tmpTableSuffix = '_tmp';

    /**
     * Catalog tmp category index table name
     */
    private $additionalTableSuffix = '_replica';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string[]
     */
    private $mainTmpTable;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->connection = $resource->getConnection();
    }

    /**
     * Return validated table name
     *
     * @param string|string[] $table
     * @return string
     */
    private function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    /**
     * Create table based on main table
     *
     * @param string $mainTableName
     * @param string $newTableName
     *
     * @return void
     */
    private function createTable($mainTableName, $newTableName)
    {
        if (!$this->connection->isTableExists($newTableName)) {
            $this->connection->createTable(
                $this->connection->createTableByDdl($mainTableName, $newTableName)
            );
        }
    }

    /**
     * Drop table
     *
     * @param string $tableName
     *
     * @return void
     */
    private function dropTable($tableName)
    {
        if ($this->connection->isTableExists($tableName)) {
            $this->connection->dropTable($tableName);
        }
    }

    /**
     * Create main and replica index tables for store
     *
     * @param $storeId
     *
     * @return void
     */
    public function createTablesForStore(int $storeId)
    {
        $mainTableName = $this->getMainTable($storeId);
        $this->createTable($this->getTable($this->mainIndexTable), $mainTableName);

        $mainReplicaTableName = $this->getMainTable($storeId) . $this->additionalTableSuffix;
        $this->createTable($this->getTable($this->mainIndexTable), $mainReplicaTableName);
    }

    /**
     * Drop main and replica index tables for store
     *
     * @param $storeId
     *
     * @return void
     */
    public function dropTablesForStore(int $storeId)
    {
        $mainTableName = $this->getMainTable($storeId);
        $this->dropTable($mainTableName);

        $mainReplicaTableName = $this->getMainTable($storeId) . $this->additionalTableSuffix;
        $this->dropTable($mainReplicaTableName);
    }

    /**
     * Return main index table name
     *
     * @param $storeId
     *
     * @return string
     */
    public function getMainTable(int $storeId)
    {
        return $this->getTable($this->mainIndexTable) . $this->mainIndexTableStoreSuffix . $storeId;
    }

    /**
     * Return replica index table name
     *
     * @param $storeId
     *
     * @return string
     */
    public function getMainReplicaTable(int $storeId)
    {
        return $this->getMainTable($storeId) . $this->additionalTableSuffix;
    }

    /**
     * Return temporary index table name
     *
     * @param $storeId
     *
     * @return string
     */
    public function getMainTmpTable(int $storeId)
    {
        if (!isset($this->mainTmpTable[$storeId])) {
            $originTableName = $this->getMainTable($storeId);
            $temporaryTableName = $this->getMainTable($storeId) . $this->tmpTableSuffix;
            $this->connection->createTemporaryTableLike($temporaryTableName, $originTableName, true);
            $this->mainTmpTable[$storeId] = $temporaryTableName;
        }
        return $this->mainTmpTable[$storeId];
    }
}
