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
    /** Suffix for replica index table. */
    private $additionalTableSuffix = '_replica';

    /** Suffix for outdated index table. */
    private $outdatedTableSuffix = '_outdated';

    /**
     * Switch index tables from replica to active.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $tableName
     * @return void
     */
    public function switchTable(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $tableName)
    {
        $outdatedTableName = $tableName . $this->outdatedTableSuffix;
        $replicaTableName = $tableName . $this->additionalTableSuffix;

        $connection->renameTablesBatch(
            [
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
            ]
        );
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
