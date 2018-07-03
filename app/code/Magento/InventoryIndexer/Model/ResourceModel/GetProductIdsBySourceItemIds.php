<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

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
     * @var string
     */
    private $productTableName;

    /**
     * @param ResourceConnection $resource
     * @param string $productTableName
     */
    public function __construct(
        ResourceConnection $resource,
        string $productTableName
    ) {
        $this->resource = $resource;
        $this->productTableName = $productTableName;
    }

    /**
     * Get product ids related to specified source items.
     *
     * @param array $sourceItemIds
     * @return array
     */
    public function execute(array $sourceItemIds): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['source_item' => $this->resource->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
                []
            )->where(
                'source_item.' . SourceItem::ID_FIELD_NAME . ' IN (?)',
                $sourceItemIds
            )->join(
                ['product' => $this->resource->getTableName($this->productTableName)],
                'source_item.' . SourceItemInterface::SKU . ' = product.sku',
                ['product.entity_id']
            )->distinct();

        return $connection->fetchCol($select);
    }
}
