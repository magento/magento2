<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

use Magento\CatalogRule\Api\IndexerTableSwapperInterface;
use Magento\Framework\DB\Adapter\AdapterInterface as Adapter;
use Magento\Framework\App\ResourceConnection;


class IndexerTableSwapper implements IndexerTableSwapperInterface
{
    /**
     * Keys are original tables' names, values - created temporary tables.
     *
     * @var string[]
     */
    private $temporaryTables = [];

    /**
     * @var Adapter
     */
    private $adapter;

    public function __construct(ResourceConnection $resource)
    {
        $this->adapter = $resource->getConnection();
    }

    /**
     * Create temporary table based on given table to use instead of original.
     *
     * @param string $originalTableName
     *
     * @return string Created table name.
     * @throws \Throwable
     */
    private function createTemporaryTable(string $originalTableName): string
    {
        $temporaryTableName = $originalTableName . '__temp'
            . $this->generateRandomSuffix();

        $newTable = $this->adapter->createTableByDdl(
            $originalTableName,
            $temporaryTableName
        );
        $this->adapter->createTable($newTable);

        return $temporaryTableName;
    }

    /**
     * Random suffix for temporary tables not to conflict with each other.
     *
     * @return string
     */
    private function generateRandomSuffix(): string
    {
        return md5(random_bytes(64));
    }

    /**
     * @inheritDoc
     */
    public function getWorkingTableNameFor(string $originalTable): string
    {
        if (!array_key_exists($originalTable, $this->temporaryTables)) {
            $this->temporaryTables[$originalTable]
                = $this->createTemporaryTable($originalTable);
        }

        return $this->temporaryTables[$originalTable];
    }

    /**
     * @inheritDoc
     */
    public function swapIndexTables(array $originalTablesNames)
    {
        $toRename = [];
        /** @var string[] $toDrop */
        $toDrop = [];
        /** @var string[] $temporaryTablesRenamed */
        $temporaryTablesRenamed = [];
        //Renaming temporary tables to original tables' names, dropping old
        //tables.
        foreach ($originalTablesNames as $tableName) {
            $temporaryOriginalName = $tableName . $this->generateRandomSuffix();
            $temporaryTableName = $this->getWorkingTableNameFor($tableName);
            $toRename[] = [
                'oldName' => $tableName,
                'newName' => $temporaryOriginalName
            ];
            $toRename[] = [
                'oldName' => $temporaryTableName,
                'newName' => $tableName
            ];
            $toDrop[] = $temporaryOriginalName;
            $temporaryTablesRenamed[] = $temporaryTableName;
        }

        //Swapping tables.
        $this->adapter->renameTablesBatch($toRename);
        //Cleaning up.
        foreach ($temporaryTablesRenamed as $tableName) {
            unset($this->temporaryTables[$tableName]);
        }
        //Removing old ones.
        foreach ($toDrop as $tableName) {
            $this->adapter->dropTable($tableName);
        }
    }
}