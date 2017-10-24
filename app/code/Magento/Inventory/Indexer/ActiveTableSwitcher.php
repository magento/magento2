<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Inventory\Indexer;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Logic for switching active and replica index tables.
 */
class ActiveTableSwitcher
{
    /**
     * Suffix for replica index table.
     *
     * @var string
     */
    private $additionalTableSuffix = '_replica';

    /**
     * Suffix for outdated index table.
     *
     * @var string
     */
    private $outdatedTableSuffix = '_outdated';

    /**
     * Switch index tables from replica to active.
     *
     * @param AdapterInterface $connection
     * @param array $tableNames
     *
     * @return void
     */
    public function switchTable(AdapterInterface $connection, array $tableNames)
    {
        $toRename = [];
        foreach ($tableNames as $tableName) {
            $outdatedTableName = $tableName . $this->outdatedTableSuffix;
            $replicaTableName = $tableName . $this->additionalTableSuffix;

            $renameBatch = [
                [
                    'oldName' => $tableName,
                    'newName' => $outdatedTableName
                ],
                [
                    'oldName' => $replicaTableName,
                    'newName' => $tableName
                ],
                [
                    'oldName' => $outdatedTableName,
                    'newName' => $replicaTableName
                ]
            ];
            $toRename = array_merge($toRename, $renameBatch);
        }

        if (!empty($toRename)) {
            $connection->renameTablesBatch($toRename);
        }
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getAdditionalTableName($tableName)
    {
        return $tableName . $this->additionalTableSuffix;
    }
}
