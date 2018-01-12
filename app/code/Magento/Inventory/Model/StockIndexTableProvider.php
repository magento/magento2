<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndex\Alias;
use Magento\Framework\MultiDimensionalIndex\IndexNameBuilder;
use Magento\Framework\MultiDimensionalIndex\IndexNameResolver;
use Magento\Inventory\Indexer\Stock\StockIndexer;
use Magento\InventoryApi\Api\StockIndexTableProviderInterface;

/**
 * Stock index table provider.
 */
class StockIndexTableProvider implements StockIndexTableProviderInterface
{
    /**
     * @var string
     */
    private $dimensionName = 'stock_';

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexNameResolver
     */
    private $indexNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexNameResolver $indexNameResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        IndexNameBuilder $indexNameBuilder,
        IndexNameResolver $indexNameResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexNameResolver = $indexNameResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(int $stockId): string
    {
        $indexName = $this->indexNameBuilder
            ->setIndexId(StockIndexer::INDEXER_ID)
            ->addDimension($this->dimensionName, (string)$stockId)
            ->setAlias(Alias::ALIAS_MAIN)
            ->build();

        $tableName = $this->indexNameResolver->resolveName($indexName);

        return $this->resourceConnection->getTableName($tableName);
    }
}
