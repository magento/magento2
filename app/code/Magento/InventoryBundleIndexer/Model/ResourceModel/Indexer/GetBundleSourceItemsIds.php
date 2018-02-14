<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Model\ResourceModel\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;

/**
 * Get bundle source item ids by source item ids.
 * Two cases:
 * - if product type is bundle;
 * - if parent product type is bundle.
 */
class GetBundleSourceItemsIds
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $sourceItemsIds
     * @return array
     */
    public function execute(array $sourceItemsIds): array
    {
        $inventorySourceItemTable = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);
        $productTable = $this->resourceConnection->getTableName('catalog_product_entity');

        $select = $this->resourceConnection->getConnection()->select();
        $select->from(
            ['source_item' => $inventorySourceItemTable],
            []
        )->joinInner(
            ['product' => $productTable],
            'source_item.sku = product.sku',
            []
        )->joinInner(
            ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
            'product.entity_id = relation.child_id',
            []
        )->joinInner(
            ['bundle_product' => $productTable],
            'bundle_product.entity_id = relation.parent_id',
            []
        )->joinInner(
            ['bundle_source_item' => $inventorySourceItemTable],
            'bundle_source_item.sku = bundle_product.sku',
            ['bundle_source_item.' . SourceItem::ID_FIELD_NAME]
        )->where(
            '(source_item.' . SourceItem::ID_FIELD_NAME . ' in (?))',
            $sourceItemsIds
        )->orWhere(
            '(bundle_source_item.' . SourceItem::ID_FIELD_NAME . ' in (?))' .
            ' AND (bundle_product.' . ProductInterface::TYPE_ID . ' = "' . ProductType::TYPE_BUNDLE . '")',
            $sourceItemsIds
        )->group(SourceItem::ID_FIELD_NAME);

        $bundleSourceItemIds = $select->query()->fetchAll();

        return $bundleSourceItemIds;
    }
}
