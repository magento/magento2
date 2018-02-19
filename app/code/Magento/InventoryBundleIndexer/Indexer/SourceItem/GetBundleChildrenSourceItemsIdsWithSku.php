<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;

/**
 * Get bundle children source item ids by source item ids.
 * Find product by source item sku and check if product has parent product bundle. If true - find all children source
 * items for this bundle and return them with bundle sku.
 */
class GetBundleChildrenSourceItemsIdsWithSku
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetBundleIdsBySourceItemIds
     */
    private $getBundleIdsBySourceItemIds;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetBundleIdsBySourceItemIds $getBundleIdsBySourceItemIds
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetBundleIdsBySourceItemIds $getBundleIdsBySourceItemIds
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getBundleIdsBySourceItemIds = $getBundleIdsBySourceItemIds;
    }

    /**
     * @param array $sourceItemsIds
     *
     * @return array
     */
    public function execute(array $sourceItemsIds): array
    {
        $bundleSourceItemIds = [];
        $bundleIds = $this->getBundleIdsBySourceItemIds->execute($sourceItemsIds);

        if (count($sourceItemsIds)) {
            $bundleSourceItemIds = $this->getBundleChildrenSourceItemsIdsWithSku($bundleIds);
        }

        return $bundleSourceItemIds;
    }

    /**
     * @param array $bundleIds
     *
     * @return array
     */
    private function getBundleChildrenSourceItemsIdsWithSku(array $bundleIds): array
    {
        $select = $this->resourceConnection->getConnection()->select();
        $select->from(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            []
        )->joinInner(
            ['source_item' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
            'source_item.sku = product.sku',
            ['source_item.' . SourceItem::ID_FIELD_NAME]
        )->joinInner(
            ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
            'product.entity_id = relation.child_id',
            []
        )->joinInner(
            ['product_bundle' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'product_bundle.entity_id = relation.parent_id',
            ['product_bundle.sku']
        )->where(
            'relation.parent_id in (?)',
            $bundleIds
        )->distinct();

        $bundleChildren = $select->query()->fetchAll();
        $bundleChildrenSourceItemsIdsBySku = [];

        foreach ($bundleChildren as $bundleChild) {
            $bundleChildrenSourceItemsIdsBySku[$bundleChild['sku']][] = $bundleChild[SourceItem::ID_FIELD_NAME];
        }

        return $bundleChildrenSourceItemsIdsBySku;
    }
}
