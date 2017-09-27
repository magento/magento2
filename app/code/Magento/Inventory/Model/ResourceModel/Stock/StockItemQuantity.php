<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\Stock;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexNameResolverInterface;
use Magento\Inventory\Indexer\StockItem\IndexStructure as StockItemIndex;
use Magento\Inventory\Indexer\StockItemIndexerInterface;

/**
 * The resource model responsible for retrieving StockItem Quantity.
 * Used by Service Contracts that are agnostic to the Data Access Layer.
 */
class StockItemQuantity
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexNameResolverInterface $indexNameResolver
     */
    public function __construct(
        ResourceConnection $resource,
        IndexNameBuilder $indexNameBuilder,
        IndexNameResolverInterface $indexNameResolver
    ) {
        $this->resource = $resource;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexNameResolver = $indexNameResolver;
    }

    /**
     * Given a product sku and a stock id, return stock item quantity.
     *
     * @param string $sku
     * @param int $stockId
     * @return float
     */
    public function execute(string $sku, int $stockId): float
    {
        $indexName = $this->indexNameBuilder
            ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
            ->addDimension('stock_', (string) $stockId)
            ->setAlias(Alias::ALIAS_MAIN)
            ->create();

        $stockItemTableName = $this->indexNameResolver->resolveName($indexName);

        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from($stockItemTableName, [StockItemIndex::QUANTITY])
            ->where(StockItemIndex::SKU . '=?', $sku);

        $stockItemQty = $connection->fetchOne($select);
        if (false === $stockItemQty) {
            $stockItemQty = 0;
        }

        return (float) $stockItemQty;
    }
}
