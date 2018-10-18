<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMultiDimensionalIndexerApi\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @inheritdoc
 */
class IndexTableSwitcher implements IndexTableSwitcherInterface
{
    /**
     * TODO: move to separate configurable interface (https://github.com/magento-engcom/msi/issues/213)
     * Suffix for replica index table
     *
     * @var string
     */
    private $replicaTableSuffix = '_replica';

    /**
     * TODO: move to separate configurable interface (https://github.com/magento-engcom/msi/issues/213)
     * Suffix for outdated index table
     *
     * @var string
     */
    private $outdatedTableSuffix = '_outdated';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @param ResourceConnection $resourceConnection
     * @param IndexNameResolverInterface $indexNameResolver
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        IndexNameResolverInterface $indexNameResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->indexNameResolver = $indexNameResolver;
    }

    /**
     * @inheritdoc
     */
    public function switch(IndexName $indexName, string $connectionName): void
    {
        $connection = $this->resourceConnection->getConnection($connectionName);
        $tableName = $this->indexNameResolver->resolveName($indexName);

        $this->switchTable($connection, [$tableName]);
    }

    /**
     * Switch index tables from replica to active
     *
     * @param AdapterInterface $connection
     * @param array $tableNames
     * @return void
     */
    private function switchTable(AdapterInterface $connection, array $tableNames)
    {
        $toRename = [];
        foreach ($tableNames as $tableName) {
            $outdatedTableName = $tableName . $this->outdatedTableSuffix;
            $replicaTableName = $tableName . $this->replicaTableSuffix;

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
                ]
            ];
            $toRename = array_merge($toRename, $renameBatch);
        }

        if (!empty($toRename)) {
            $connection->renameTablesBatch($toRename);
        }
    }
}
