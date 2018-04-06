<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Category\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\DB\Adapter\AdapterInterface;

class TableMaintainer
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * Catalog tmp category index table name
     */
    private $tmpTableSuffix = '_tmp';

    /**
     * Catalog tmp category index table name
     */
    private $additionalTableSuffix = '_replica';

    /**
     * @var string[]
     */
    private $mainTmpTable;

    /**
     * @param ResourceConnection $resource
     * @param TableResolver $tableResolver
     */
    public function __construct(
        ResourceConnection $resource,
        TableResolver $tableResolver
    ) {
        $this->resource = $resource;
        $this->tableResolver = $tableResolver;
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
     * Return main index table name
     *
     * @param $storeId
     *
     * @return string
     */
    public function getMainTable(int $storeId)
    {
        $catalogCategoryProductDimension = new Dimension(\Magento\Store\Model\Store::ENTITY, $storeId);

        return $this->tableResolver->resolve(TableResolver::MAIN_INDEX_TABLE, [$catalogCategoryProductDimension]);
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
        $this->createTable($this->getTable(TableResolver::MAIN_INDEX_TABLE), $mainTableName);

        $mainReplicaTableName = $this->getMainTable($storeId) . $this->additionalTableSuffix;
        $this->createTable($this->getTable(TableResolver::MAIN_INDEX_TABLE), $mainReplicaTableName);
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
     * Create temporary index table for store
     *
     * @param $storeId
     *
     * @return void
     */
    public function createMainTmpTable(int $storeId)
    {
        if (!isset($this->mainTmpTable[$storeId])) {
            $originTableName = $this->getMainTable($storeId);
            $temporaryTableName = $this->getMainTable($storeId) . $this->tmpTableSuffix;
            $this->connection->createTemporaryTableLike($temporaryTableName, $originTableName, true);
            $this->mainTmpTable[$storeId] = $temporaryTableName;
        }
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
        return $this->mainTmpTable[$storeId];
    }
}
