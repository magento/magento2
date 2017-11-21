<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\Stock;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndex\Alias;
use Magento\Framework\MultiDimensionalIndex\IndexNameBuilder;
use Magento\Framework\MultiDimensionalIndex\IndexNameResolverInterface;
use Magento\Inventory\Indexer\IndexStructure;

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
            ->setIndexId('inventory_stock')
            ->addDimension('stock_', (string)$stockId)
            ->setAlias(Alias::ALIAS_MAIN)
            ->build();
        $stockItemTableName = $this->indexNameResolver->resolveName($indexName);

        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($stockItemTableName, [IndexStructure::QUANTITY])
            ->where(IndexStructure::SKU . '=?', $sku);

        $stockItemQty = $connection->fetchOne($select);
        if (false === $stockItemQty) {
            $stockItemQty = 0;
        }
        return (float)$stockItemQty;
    }
}
