<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;

class GetBundleIdsBySourceItemIds
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
        $productTable = $this->resourceConnection->getTableName('catalog_product_entity');
        $select = $this->resourceConnection->getConnection()->select();
        $select->from(
            ['source_item' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
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
            ['bundle_product.entity_id']
        )->where(
            'source_item.' . SourceItem::ID_FIELD_NAME . ' in (?)',
            $sourceItemsIds
        )->where(
            'bundle_product.' . ProductInterface::TYPE_ID . ' = "' . ProductType::TYPE_BUNDLE . '"'
        );

        $bundleIds = $select->query()->fetchAll();

        $bundleIds = array_map(
            function ($itemId) {
                return $itemId['entity_id'];
            },
            $bundleIds
        );

        return $bundleIds;
    }
}
