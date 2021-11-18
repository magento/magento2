<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Indexer;

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
     * @return void
     */
    public function switchTable(\Magento\Framework\DB\Adapter\AdapterInterface $connection, array $tableNames)
    {
        $toRename = [];
        foreach ($tableNames as $tableName) {
            $outdatedTableName = $tableName . $this->outdatedTableSuffix;
            $replicaTableName = $tableName . $this->additionalTableSuffix;

            $tableComment = $connection->showTableStatus($tableName)['Comment'] ?? '';
            $replicaComment = $connection->showTableStatus($replicaTableName)['Comment'] ?? '';

            $renameBatch = [
                [
                    'oldName' => $tableName,
                    'newName' => $outdatedTableName,
                ],
                [
                    'oldName' => $replicaTableName,
                    'newName' => $tableName,
                ],
                [
                    'oldName' => $outdatedTableName,
                    'newName' => $replicaTableName,
                ],
            ];

            $toRename[] = $renameBatch;

            if (!empty($toRename) && $replicaComment !== '' && $tableComment !== $replicaComment) {
                $connection->changeTableComment($tableName, $replicaComment);
                $connection->changeTableComment($replicaTableName, $tableComment);
            }
        }
        $toRename = array_merge([], ...$toRename);

        if (!empty($toRename)) {
            $connection->renameTablesBatch($toRename);
        }
    }

    /**
     * Returns table name with additional suffix
     *
     * @param string $tableName
     * @return string
     */
    public function getAdditionalTableName($tableName)
    {
        return $tableName . $this->additionalTableSuffix;
    }
}
