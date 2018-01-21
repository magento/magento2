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
use Magento\Framework\MultiDimensionalIndexer\IndexNameResolverInterface;
use Magento\Inventory\Model\GetStockItemQuantityInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;

/**
 * @inheritdoc
 */
class GetStockItemQuantity implements GetStockItemQuantityInterface
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
     * @inheritdoc
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
            ->where(IndexStructure::SKU . ' = ?', $sku);

        $stockItemQty = $connection->fetchOne($select);
        if (false === $stockItemQty) {
            $stockItemQty = 0;
        }
        return (float)$stockItemQty;
    }
}
