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
        $tableComment = '';
        $replicaComment = '';
        foreach ($tableNames as $tableName) {
            $outdatedTableName = $tableName . $this->outdatedTableSuffix;
            $replicaTableName = $tableName . $this->additionalTableSuffix;

            $tableCreateQuery = 'SHOW CREATE TABLE ' . $tableName;
            $tableCreateResult = $connection->fetchRow($tableCreateQuery);

            if (is_array($tableCreateResult)) {
                $tableComment = $this->getTableComment($tableCreateResult);
            }

            $replicaCreateQuery = 'SHOW CREATE TABLE ' . $replicaTableName;
            $replicaCreateResult = $connection->fetchRow($replicaCreateQuery);

            if (is_array($replicaCreateResult)) {
                $replicaComment = $this->getTableComment($replicaCreateResult);
            }

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

            $toRename = $this->mergeRenameTables($renameBatch, $toRename);

            if (!empty($toRename) && $replicaComment !== '' && $tableComment !== $replicaComment) {
                $changeTableComment   = sprintf("ALTER TABLE %s COMMENT=%s", $tableName, $replicaComment);
                $connection->query($changeTableComment);
                $changeReplicaComment = sprintf("ALTER TABLE %s COMMENT=%s", $replicaTableName, $tableComment);
                $connection->query($changeReplicaComment);
            }
        }

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

    /**
     * Returns table comment
     *
     * @param array $tableCreateResult
     * @return string
     */
    private function getTableComment($tableCreateResult)
    {
        $tableComment = '';
        $replicaCommentPosition = strpos((string )end($tableCreateResult), "COMMENT=");
        if ($replicaCommentPosition) {
            $tableComment = substr((string )end($tableCreateResult), $replicaCommentPosition + 8);
        }
        return $tableComment;
    }

    /**
     * Merges two arrays
     *
     * @param array $renameBatch
     * @param array $toRename
     * @return array
     */
    private function mergeRenameTables($renameBatch, $toRename)
    {
        foreach ($renameBatch as $batch) {
            if (!in_array($batch, $toRename)) {
                array_push($toRename, $batch);
            }
        }
        return $toRename;
    }
}
