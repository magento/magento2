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
     * @var string
     */
    private $tableNameSourceItem;

    /**
     * @var string
     */
    private $sourceItemIdFieldName;

    /**
     * GetProductIdsByStockIds constructor.
     *
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     * @param string $tableNameSourceItem
     * @param string $sourceItemIdFieldName
     */
    public function __construct(
        ResourceConnection $resource,
        MetadataPool $metadataPool,
        $tableNameSourceItem,
        $sourceItemIdFieldName
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->tableNameSourceItem = $tableNameSourceItem;
        $this->sourceItemIdFieldName = $sourceItemIdFieldName;
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
        $sourceItemTable = $this->resource->getTableName($this->tableNameSourceItem);
        $select = $connection->select()
            ->from(
                ['source_items_table' => $sourceItemTable],
                []
            )->where(
                $this->sourceItemIdFieldName . ' IN (?)',
                $sourceItemIds
            )->join(
                ['product_table' => $this->resource->getTableName('catalog_product_entity')],
                'source_items_table.sku = product_table.sku',
                [$productLinkField]
            )->distinct(true);

        return $connection->fetchCol($select);
    }
}
