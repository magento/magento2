<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Inventory\Indexer\IndexName;
use Magento\Inventory\Indexer\IndexNameResolverInterface;
use Magento\Inventory\Indexer\IndexStructureInterface;

/**
 * @inheritdoc
 */
class IndexStructure implements IndexStructureInterface
{
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
    public function create(IndexName $indexName, string $connectionName = ResourceConnection::DEFAULT_CONNECTION): void
    {
        $connection = $this->resourceConnection->getConnection($connectionName);
        $tableName = $this->indexNameResolver->resolveName($indexName);

        if ($connection->isTableExists($tableName)) {
            $connection->truncateTable($tableName);
            return;
        }

        $table = $connection->newTable(
            $tableName
        )->setComment(
            'Inventory Stock item Table'
        )->addColumn(
            'stock_id',
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_PRIMARY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ],
            'Stock ID'
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            64,
            [
                Table::OPTION_PRIMARY => true,
                Table::OPTION_NULLABLE => false,
            ],
            'Sku'
        )->addColumn(
            'quantity',
            Table::TYPE_DECIMAL,
            null,
            [
                Table::OPTION_UNSIGNED => false,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
            ],
            'Quantity'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            [
                Table::OPTION_NULLABLE => true,
                Table::OPTION_UNSIGNED => true,
            ],
            'Status'
        );
        $connection->createTable($table);
    }

    /**
     * @inheritdoc
     */
    public function delete(IndexName $indexName, string $connectionName = ResourceConnection::DEFAULT_CONNECTION): void
    {
        $connection = $this->resourceConnection->getConnection($connectionName);
        $tableName = $this->indexNameResolver->resolveName($indexName);

        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }
    }
}
