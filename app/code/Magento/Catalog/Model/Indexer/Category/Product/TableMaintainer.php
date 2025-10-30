<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);
namespace Magento\Catalog\Model\Indexer\Category\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver as TableResolver;

/**
 * Class encapsulate logic of work with tables per store in Category Product indexer
 */
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
     *
     * @var string
     */
    private $tmpTableSuffix = '_tmp';

    /**
     * Catalog tmp category index table name
     *
     * @var string
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
    }

    /**
     * Get connection
     *
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if (!isset($this->connection)) {
            $this->connection = $this->resource->getConnection();
        }
        return $this->connection;
    }

    /**
     * Expose connection so callers can use the same adapter instance that created temporary tables.
     *
     * @return AdapterInterface
     */
    public function getSameAdapterConnection(): AdapterInterface
    {
        return $this->getConnection();
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
     *
     * @throws \Zend_Db_Exception
     */
    private function createTable($mainTableName, $newTableName)
    {
        if (!$this->getConnection()->isTableExists($newTableName)) {
            $this->getConnection()->createTable(
                $this->getConnection()->createTableByDdl($mainTableName, $newTableName)
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
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
    }

    /**
     * Return main index table name
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getMainTable(int $storeId)
    {
        $catalogCategoryProductDimension = new Dimension(\Magento\Store\Model\Store::ENTITY, $storeId);

        return $this->tableResolver->resolve(AbstractAction::MAIN_INDEX_TABLE, [$catalogCategoryProductDimension]);
    }

    /**
     * Create main and replica index tables for store
     *
     * @param int $storeId
     *
     * @return void
     *
     * @throws \Zend_Db_Exception
     */
    public function createTablesForStore(int $storeId)
    {
        $mainTableName = $this->getMainTable($storeId);
        //Create index table for store based on main replica table
        //Using main replica table is necessary for backward capability and TableResolver plugin work
        $this->createTable(
            $this->getTable(AbstractAction::MAIN_INDEX_TABLE . $this->additionalTableSuffix),
            $mainTableName
        );

        $mainReplicaTableName = $this->getMainTable($storeId) . $this->additionalTableSuffix;
        //Create replica table for store based on main replica table
        $this->createTable(
            $this->getTable(AbstractAction::MAIN_INDEX_TABLE . $this->additionalTableSuffix),
            $mainReplicaTableName
        );
    }

    /**
     * Drop main and replica index tables for store
     *
     * @param int $storeId
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
     * @param int $storeId
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
     * @param int $storeId
     *
     * @return void
     */
    public function createMainTmpTable(int $storeId)
    {
        if (!isset($this->mainTmpTable[$storeId])) {
            $originTableName = $this->getMainTable($storeId);
            $temporaryTableName = $this->getMainTable($storeId) . $this->tmpTableSuffix;
            $this->getConnection()->createTemporaryTableLike($temporaryTableName, $originTableName, true);
            $this->mainTmpTable[$storeId] = $temporaryTableName;
        }
    }

    /**
     * Return temporary index table name
     *
     * @param int $storeId
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMainTmpTable(int $storeId)
    {
        if (!isset($this->mainTmpTable[$storeId])) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Temporary table does not exist'));
        }
        return $this->mainTmpTable[$storeId];
    }
}
