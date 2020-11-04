<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Indexer;

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
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
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

            $tableCommentPosition = strpos((string )end($tableCreateResult), "COMMENT=");
            if ($tableCommentPosition) {
                $tableComment = substr((string )end($tableCreateResult), $tableCommentPosition + 8);
            }

            $replicaCreateQuery = 'SHOW CREATE TABLE ' . $replicaTableName;
            $replicaCreateResult = $connection->fetchRow($replicaCreateQuery);

            $replicaCommentPosition = strpos((string )end($replicaCreateResult), "COMMENT=");
            if ($replicaCommentPosition) {
                $replicaComment = substr((string )end($replicaCreateResult), $replicaCommentPosition + 8);
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
            $toRename = array_merge($toRename, $renameBatch);
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
}
