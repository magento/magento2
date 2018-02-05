<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Resolves ids of related (parent) products for a set of child product ids.
 */
class ParentProductsResolver
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Get IDs of parent products by their child IDs.
     *
     * Returns identifiers of parent product from the catalog_product_relation.
     * Please note that returned ids don't contain ids of passed child products.
     *
     * @param int[] $childProductIds
     * @return int[]
     * @throws \Exception if metadataPool doesn't contain metadata for ProductInterface
     * @throws \DomainException
     */
    public function getParentProductIds(array $childProductIds)
    {
        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $fieldForParent = $metadata->getLinkField();

        $connection = $this->resourceConnection->getConnection();

        $select = $connection
            ->select()
            ->from(['relation' => $this->resourceConnection->getTableName('catalog_product_relation')], [])
            ->distinct(true)
            ->where('child_id IN (?)', $childProductIds)
            ->join(
                ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'relation.parent_id = cpe.'.$fieldForParent,
                ['cpe.entity_id']
            );

        return $connection->fetchCol($select);
    }
}
