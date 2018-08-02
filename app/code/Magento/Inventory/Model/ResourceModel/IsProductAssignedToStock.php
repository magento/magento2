<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel;

use Magento\InventoryApi\Model\IsProductAssignedToStockInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

class IsProductAssignedToStock implements IsProductAssignedToStockInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['stock_source_link' => $this->resource->getTableName(StockSourceLink::TABLE_NAME_STOCK_SOURCE_LINK)]
            )->join(
                ['inventory_source_item' => $this->resource->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
                'inventory_source_item.' . SourceItemInterface::SOURCE_CODE . '
                = stock_source_link.' . SourceItemInterface::SOURCE_CODE,
                []
            )->where(
                'stock_source_link.' . StockSourceLinkInterface::STOCK_ID . ' = ?',
                $stockId
            )->where(
                'inventory_source_item.' . SourceItemInterface::SKU . ' = ?',
                $sku
            );

        return (bool)$connection->fetchOne($select);
    }
}
