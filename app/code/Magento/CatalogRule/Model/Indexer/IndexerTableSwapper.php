<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

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
<<<<<<< HEAD
     * @throws \Throwable
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
            $this->temporaryTables[$originalTable]
                = $this->createTemporaryTable($originalTable);
=======
            $this->temporaryTables[$originalTable] = $this->createTemporaryTable($originalTable);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
                'newName' => $temporaryOriginalName
            ];
            $toRename[] = [
                'oldName' => $temporaryTableName,
                'newName' => $tableName
=======
                'newName' => $temporaryOriginalName,
            ];
            $toRename[] = [
                'oldName' => $temporaryTableName,
                'newName' => $tableName,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
