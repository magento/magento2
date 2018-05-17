<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
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
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @var string
     */
    private $productSkuColumn;

    /**
     * @var string
     */
    private $productInterfaceClassName;

    /**
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param IndexStructure $indexStructure
     * @param $productSkuColumn
     * @param $productInterfaceClassName
     */
    public function __construct(
        ResourceConnection $resource,
        MetadataPool $metadataPool,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        DefaultStockProviderInterface $defaultStockProvider,
        IndexStructure $indexStructure,
        $productSkuColumn,
        $productInterfaceClassName
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->indexStructure = $indexStructure;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->productSkuColumn = $productSkuColumn;
        $this->productInterfaceClassName = $productInterfaceClassName;
    }

    /**
     * @param array $stockIds
     * @return array
     * @throws \Exception in case product entity type hasn't been initialize.
     */
    public function execute(array $stockIds): array
    {
        $productIds = [[]];
        foreach ($stockIds as $stockId) {
            if ($this->defaultStockProvider->getId() === (int)$stockId) {
                continue;
            }
            $stockIndexTableName = $this->stockIndexTableNameResolver->execute($stockId);
            $entityMetadata = $this->metadataPool->getMetadata($this->productInterfaceClassName);
            $linkField = $entityMetadata->getLinkField();
            $connection = $this->resource->getConnection();
            $sql = $connection->select()
                ->from(['main' => $stockIndexTableName], [])
                ->join(
                    ['product' => $this->resource->getTableName('catalog_product_entity')],
                    'product.' . $this->productSkuColumn . '=main.' . $this->productSkuColumn,
                    [$linkField]
                );

            if ($connection->isTableExists($stockIndexTableName)) {
                $productIds[] = $connection->fetchCol($sql);
            }
        }
        $productIds = array_merge(...$productIds);

        return array_unique($productIds);
    }
}
