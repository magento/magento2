<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\Framework\MultiDimensionalIndexer\IndexNameResolver;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;

/**
 * @inheritdoc
 */
class StockIndexTableNameResolver implements StockIndexTableNameResolverInterface
{
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
     * @var string
     */
    private $dimensionName;

    /**
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexNameResolver $indexNameResolver
     * @param ResourceConnection $resourceConnection
     * @param string $dimensionName
     */
    public function __construct(
        IndexNameBuilder $indexNameBuilder,
        IndexNameResolver $indexNameResolver,
        ResourceConnection $resourceConnection,
        string $dimensionName
    ) {
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexNameResolver = $indexNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->dimensionName = $dimensionName;
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
