<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Framework\App\ResourceConnection;

/**
 * @inheritDoc
 */
class IndexerTableSwapper implements IndexerTableSwapperInterface
{
    /**
     * Keys are original tables' names, values - created temporary tables.
     *
     * @var string[]
     */
    private $temporaryTables = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resourceConnection = $resource;
    }

    /**
     * Create temporary table based on given table to use instead of original.
     *
     * @param string $originalTableName
     *
     * @return string Created table name.
     */
    private function createTemporaryTable(string $originalTableName): string
    {
        $temporaryTableName = $this->resourceConnection->getTableName(
            $originalTableName . '__temp' . $this->generateRandomSuffix()
        );

        $this->resourceConnection->getConnection()->query(
            sprintf(
                'create table %s like %s',
                $temporaryTableName,
                $this->resourceConnection->getTableName($originalTableName)
            )
        );

        return $temporaryTableName;
    }

    /**
     * Random suffix for temporary tables not to conflict with each other.
     *
     * @return string
     */
    private function generateRandomSuffix(): string
    {
        return bin2hex(random_bytes(4));
    }

    /**
     * @inheritDoc
     */
    public function getWorkingTableName(string $originalTable): string
    {
        $originalTable = $this->resourceConnection->getTableName($originalTable);
        if (!array_key_exists($originalTable, $this->temporaryTables)) {
            $this->temporaryTables[$originalTable] = $this->createTemporaryTable($originalTable);
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
            $tableName = $this->resourceConnection->getTableName($tableName);
            $temporaryOriginalName = $this->resourceConnection->getTableName(
                $tableName . $this->generateRandomSuffix()
            );
            $temporaryTableName = $this->getWorkingTableName($tableName);
            $toRename[] = [
                'oldName' => $tableName,
                'newName' => $temporaryOriginalName,
            ];
            $toRename[] = [
                'oldName' => $temporaryTableName,
                'newName' => $tableName,
            ];
            $toDrop[] = $temporaryOriginalName;
            $temporaryTablesRenamed[] = $tableName;
        }

        //Swapping tables.
        $this->resourceConnection->getConnection()->renameTablesBatch($toRename);
        //Cleaning up.
        foreach ($temporaryTablesRenamed as $tableName) {
            unset($this->temporaryTables[$tableName]);
        }
        //Removing old ones.
        foreach ($toDrop as $tableName) {
            $this->resourceConnection->getConnection()->dropTable($tableName);
        }
    }
}
