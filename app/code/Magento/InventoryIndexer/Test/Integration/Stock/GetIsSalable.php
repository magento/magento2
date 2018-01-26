<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Stock;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Get is salable for product.
 */
class GetIsSalable
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @param ResourceConnection $resource
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     */
    public function __construct(
        ResourceConnection $resource,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver
    ) {
        $this->resource = $resource;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        $tableName = $this->stockIndexTableNameResolver->execute($stockId);
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($tableName, [IndexStructure::IS_SALABLE])
            ->where(IndexStructure::SKU . ' = ?', $sku);

        $isSalable = $connection->fetchOne($select);

        return (bool)$isSalable;
    }
}
