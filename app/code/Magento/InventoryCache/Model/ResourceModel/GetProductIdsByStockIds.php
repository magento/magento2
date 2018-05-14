<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexName;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\Framework\MultiDimensionalIndexer\IndexNameResolverInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

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
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     * @param IndexNameResolverInterface $indexNameResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexStructure $indexStructure
     */
    public function __construct(
        ResourceConnection $resource,
        MetadataPool $metadataPool,
        IndexNameResolverInterface $indexNameResolver,
        DefaultStockProviderInterface $defaultStockProvider,
        IndexNameBuilder $indexNameBuilder,
        IndexStructure $indexStructure
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->indexNameResolver = $indexNameResolver;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexStructure = $indexStructure;
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
            $indexName = $this->getIndexName($stockId);
            if (!$this->indexStructure->isExist($indexName, ResourceConnection::DEFAULT_CONNECTION)) {
                continue;
            }
            $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
            $linkField = $entityMetadata->getLinkField();
            $connection = $this->resource->getConnection();
            $sql = $connection->select()
                ->from(['main' => $this->indexNameResolver->resolveName($indexName)], [])
                ->join(
                    ['product' => $this->resource->getTableName('catalog_product_entity')],
                    'product.' . ProductInterface::SKU . '=main.' . ProductInterface::SKU,
                    [$linkField]
                );
            $productIds[] = $connection->fetchCol($sql);
        }
        $productIds = array_merge(...$productIds);

        return array_unique($productIds);
    }

    /**
     * Get index name by stock id.
     *
     * @param int $stockId
     * @return IndexName
     */
    private function getIndexName(int $stockId): IndexName
    {
        return $this->indexNameBuilder
            ->setIndexId(InventoryIndexer::INDEXER_ID)
            ->addDimension('stock_', (string)$stockId)
            ->setAlias(Alias::ALIAS_MAIN)
            ->build();
    }
}
