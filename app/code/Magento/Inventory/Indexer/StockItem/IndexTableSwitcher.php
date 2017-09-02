<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\IndexName;
use Magento\Inventory\Indexer\IndexNameResolverInterface;
use Magento\Inventory\Indexer\IndexTableSwitcherInterface;

/**
 * @inheritdoc
 */
class IndexTableSwitcher implements IndexTableSwitcherInterface
{
    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param ResourceConnection $resourceConnection
     * @param IndexNameResolverInterface $indexNameResolver
     */
    public function __construct(
        ActiveTableSwitcher $activeTableSwitcher,
        ResourceConnection $resourceConnection,
        IndexNameResolverInterface $indexNameResolver
    ) {
        $this->activeTableSwitcher = $activeTableSwitcher;
        $this->resourceConnection = $resourceConnection;
        $this->indexNameResolver = $indexNameResolver;
    }

    /**
     * @inheritdoc
     */
    public function switch(IndexName $indexName, string $connectionName = ResourceConnection::DEFAULT_CONNECTION)
    {
        $connection = $this->resourceConnection->getConnection($connectionName);
        $tableName = $this->indexNameResolver->resolveName($indexName);

        $this->activeTableSwitcher->switchTable($connection, [$tableName]);
    }
}
