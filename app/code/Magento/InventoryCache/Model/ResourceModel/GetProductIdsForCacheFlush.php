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
use Magento\Framework\MultiDimensionalIndexer\IndexNameResolverInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;

/**
 * Return product ids with removed non-default source(s).
 */
class GetProductIdsForCacheFlush
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
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     * @param IndexNameResolverInterface $indexNameResolver
     */
    public function __construct(
        ResourceConnection $resource,
        MetadataPool $metadataPool,
        IndexNameResolverInterface $indexNameResolver
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->indexNameResolver = $indexNameResolver;
    }

    /**
     * @param \Magento\Framework\MultiDimensionalIndexer\IndexName[] $indexNames
     * @return array
     * @throws \Exception in case product entity type hasn't been initialize.
     */
    public function execute(array $indexNames): array
    {
        list($mainIndexName, $replicaIndexName) = $indexNames;
        $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $entityMetadata->getLinkField();
        $connection = $this->resource->getConnection();
        $sql = $connection->select()
            ->from(['main' => $this->indexNameResolver->resolveName($mainIndexName)], [])
            ->joinLeft(
                ['replica' => $this->indexNameResolver->resolveName($replicaIndexName)],
                'main.' . IndexStructure::SKU . '=replica.' . IndexStructure::SKU,
                []
            )->where('replica.' . IndexStructure::IS_SALABLE . ' IS NULL')
            ->join(
                ['product' => $this->resource->getTableName('catalog_product_entity')],
                'product.' . ProductInterface::SKU . '=main.' . ProductInterface::SKU,
                [$linkField]
            )->distinct(true);

        return $connection->fetchCol($sql);
    }
}
