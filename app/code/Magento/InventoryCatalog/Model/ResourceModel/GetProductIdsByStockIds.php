<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * By stock ids get all product ids.
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
     * @param array $stockIds
     * @return array
     */
    public function execute(array $stockIds): array
    {
        $result = [];
        foreach ($stockIds as $stockId) {
            $stockIndexTableName = $this->stockIndexTableNameResolver->execute($stockId);
            $connection = $this->resource->getConnection();
            if ($connection->isTableExists($stockIndexTableName)) {
                $sql = $connection->select()
                    ->from(['stock_index' => $stockIndexTableName], [])
                    ->join(
                        ['product' => $this->resource->getTableName('catalog_product_entity')],
                        'product.sku = stock_index.' . IndexStructure::SKU,
                        ['product.entity_id']
                    );
                $result[] = $connection->fetchCol($sql);
            }
        }
        $result = array_merge(...$result);

        return array_unique($result);
    }
}
