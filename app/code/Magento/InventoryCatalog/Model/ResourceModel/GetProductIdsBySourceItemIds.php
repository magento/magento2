<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Inventory\Model\ResourceModel\SourceItem;

/**
 * Provides product ids related to specified source items ids.
 */
class GetProductIdsBySourceItemIds
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
     * GetProductIdsByStockIds constructor.
     *
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     */
    public function __construct(ResourceConnection $resource, MetadataPool $metadataPool)
    {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Get product ids related to specified source items.
     *
     * @param array $sourceItemIds
     * @return array
     * @throws \Exception in case catalog product entity type hasn't been initialize.
     */
    public function execute(array $sourceItemIds): array
    {
        $productLinkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $connection = $this->resource->getConnection();
        $sourceItemTable = $this->resource->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);
        $select = $connection->select()
            ->from(
                ['source_items_table' => $sourceItemTable],
                []
            )->where(
                SourceItem::ID_FIELD_NAME . ' IN (?)',
                $sourceItemIds
            )->join(
                ['product_table' => $this->resource->getTableName('catalog_product_entity')],
                'source_items_table.sku = product_table.sku',
                [$productLinkField]
            )->distinct(true);

        return $connection->fetchCol($select);
    }
}
