<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class BundleChildrenSourceItemsIdsSelectProvider
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
     * @return Select
     */
    public function execute(): Select
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
            )->joinInner(
                ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
                'relation.child_id = product.entity_id',
                []
            )->joinInner(
                ['bundle_product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'bundle_product.entity_id = relation.parent_id',
                ['bundle_product.sku']
            );

        return $select;
    }
}
