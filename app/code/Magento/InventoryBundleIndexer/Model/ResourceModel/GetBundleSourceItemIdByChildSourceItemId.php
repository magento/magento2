<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Get bundle source item id by child source item id.
 */
class GetBundleSourceItemIdByChildSourceItemId
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
     * @param int $sourceItemId
     *
     * @return null|int
     */
    public function execute(int $sourceItemId)
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
        )
            ->where('source_item.' . SourceItem::ID_FIELD_NAME . '= ?', $sourceItemId)
            ->where(
                'bundle_source_item.' . SourceInterface::SOURCE_CODE . ' = source_item.' . SourceInterface::SOURCE_CODE
            );

        $bundleSourceItemId = $select->query()->fetch()[SourceItem::ID_FIELD_NAME] ?? null;

        if (null !== $bundleSourceItemId) {
            $bundleSourceItemId = (int)$bundleSourceItemId;
        }

        return $bundleSourceItemId;
    }

}
