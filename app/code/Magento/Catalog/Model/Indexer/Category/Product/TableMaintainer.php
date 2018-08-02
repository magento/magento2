<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @param $storeId
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
     * @param $storeId
     *
     * @return void
     */
    public function createTablesForStore(int $storeId)
    {
        $mainTableName = $this->getMainTable($storeId);
        //Create index table for store based on on main replica table
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
            $this->getConnection()->createTemporaryTableLike($temporaryTableName, $originTableName, true);
            $this->mainTmpTable[$storeId] = $temporaryTableName;
        }
    }

    /**
     * Return temporary index table name
     *
     * @param $storeId
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getMainTmpTable(int $storeId)
    {
        if (!isset($this->mainTmpTable[$storeId])) {
            throw new \Exception('Temporary table does not exist');
        }
        return $this->mainTmpTable[$storeId];
    }
}
