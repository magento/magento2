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
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Get only bundle source items ids.
 */
class GetAllBundleSourceItemsIds
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
     * @return array
     */
    public function execute(): array
    {
        $select = $this->resourceConnection->getConnection()->select();
        $select
            ->from(
                ['source_item' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
                [SourceItem::ID_FIELD_NAME]
            )->joinInner(
                ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'source_item.' . SourceItemInterface::SKU . ' = product.' . ProductInterface::SKU,
                []
            )->where('product.' . ProductInterface::TYPE_ID . ' = ?', ProductType::TYPE_BUNDLE);
        $sourceItemsIds = $select->query()->fetchAll();

        return $sourceItemsIds;
    }
}
