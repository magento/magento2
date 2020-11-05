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
        $renameBatch = [];
        foreach ($tableNames as $tableName) {
            $outdatedTableName = $tableName . $this->outdatedTableSuffix;
            $replicaTableName = $tableName . $this->additionalTableSuffix;

            $tableCreateQuery = 'SHOW CREATE TABLE ' . $tableName;
            $tableCreateResult = $connection->fetchRow($tableCreateQuery);

            if (is_array($tableCreateResult)) {
                $tableCommentPosition = strpos((string )end($tableCreateResult), "COMMENT=");
                if ($tableCommentPosition) {
                    $tableComment = substr((string )end($tableCreateResult), $tableCommentPosition + 8);
                }
            }

            $replicaCreateQuery = 'SHOW CREATE TABLE ' . $replicaTableName;
            $replicaCreateResult = $connection->fetchRow($replicaCreateQuery);

            if (is_array($replicaCreateResult)) {
                $replicaCommentPosition = strpos((string )end($replicaCreateResult), "COMMENT=");
                if ($replicaCommentPosition) {
                    $replicaComment = substr((string )end($replicaCreateResult), $replicaCommentPosition + 8);
                }
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

            if (!empty($renameBatch)) {
                $this->switchTableComments($tableName, $replicaTableName, $tableComment, $replicaComment, $connection);
            }
        }
        $toRename = array_merge($toRename, $renameBatch);

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
     * Switch table comments
     *
     * @param string $tableName
     * @param string $replicaName
     * @param string $tableComment
     * @param string $replicaComment
     * @param AdapterInterface $connection
     */
    private function switchTableComments($tableName, $replicaName, $tableComment, $replicaComment, $connection)
    {
        if ($tableComment !== '' && $tableComment !== $replicaComment) {
            $changeTableComment   = sprintf("ALTER TABLE %s COMMENT=%s", $tableName, $replicaComment);
            $connection->query($changeTableComment);
            $changeReplicaComment = sprintf("ALTER TABLE %s COMMENT=%s", $replicaName, $tableComment);
            $connection->query($changeReplicaComment);
        }
    }
}
