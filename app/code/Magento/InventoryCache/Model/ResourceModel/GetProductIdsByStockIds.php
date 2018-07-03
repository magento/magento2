<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Get product ids for given stock form index table.
 */
class GetProductIdsByStockIds
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
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var string
     */
    private $productTableName;

    /**
     * @param ResourceConnection $resource
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param string $productTableName
     */
    public function __construct(
        ResourceConnection $resource,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        DefaultStockProviderInterface $defaultStockProvider,
        string $productTableName
    ) {
        $this->resource = $resource;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->productTableName = $productTableName;
    }

    /**
     * @param array $stockIds
     * @return array
     */
    public function execute(array $stockIds): array
    {
        $productIds = [[]];
        foreach ($stockIds as $stockId) {
            if ($this->defaultStockProvider->getId() === (int)$stockId) {
                continue;
            }
            $stockIndexTableName = $this->stockIndexTableNameResolver->execute($stockId);
            $connection = $this->resource->getConnection();

            if ($connection->isTableExists($stockIndexTableName)) {
                $sql = $connection->select()
                    ->from(['stock_index' => $stockIndexTableName], [])
                    ->join(
                        ['product' => $this->resource->getTableName($this->productTableName)],
                        'product.sku = stock_index.' . IndexStructure::SKU,
                        ['product.entity_id']
                    );
                $productIds[] = $connection->fetchCol($sql);
            }
        }
        $productIds = array_merge(...$productIds);

        return array_unique($productIds);
    }
}
