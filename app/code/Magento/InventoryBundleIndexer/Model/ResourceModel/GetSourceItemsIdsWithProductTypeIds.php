<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Fetch source items accordance to product type by source items ids.
 */
class GetSourceItemsIdsWithProductTypeIds
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
     * @param array $sourceItemIds
     *
     * @return array
     */
    public function execute(array $sourceItemIds): array
    {
        $select = $this->resourceConnection->getConnection()->select();
        $select
            ->from(
                ['source_item' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
                [SourceItem::ID_FIELD_NAME]
            )->joinInner(
                ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'source_item.' . SourceItemInterface::SKU . ' = product.' . ProductInterface::SKU,
                'product.' . ProductInterface::TYPE_ID
            )->where('source_item.' . SourceItem::ID_FIELD_NAME . ' in (?)', $sourceItemIds);
        $sourceItemsIdsWithTypeIds = $select->query()->fetchAll();

        return $sourceItemsIdsWithTypeIds;
    }
}
