<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Returns all assigned Stock ids by given Source Item ids
 */
class GetPartialReindexData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SkuListInStockToUpdateFactory
     */
    private $skuListInStockToUpdateFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SkuListInStockToUpdateFactory $skuListInStockToUpdateFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SkuListInStockToUpdateFactory $skuListInStockToUpdateFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->skuListInStockToUpdateFactory = $skuListInStockToUpdateFactory;
    }

    /**
     * Returns all assigned Stock ids by given Source Item ids
     *
     * @param int[] $sourceItemIds
     * @return SkuListInStockToUpdate[] List of stock id to sku1,sku2 assignment
     */
    public function execute(array $sourceItemIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $sourceStockLinkTable = $this->resourceConnection->getTableName(
            StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK
        );
        $sourceItemTable = $this->resourceConnection->getTableName(
            SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM
        );

        $select = $connection
            ->select()
            ->from(
                ['source_item' => $sourceItemTable],
                [
                    SourceItemInterface::SKU =>
                        sprintf("GROUP_CONCAT(DISTINCT %s SEPARATOR ',')", 'source_item.' . SourceItemInterface::SKU)
                ]
            )->joinInner(
                ['stock_source_link' => $sourceStockLinkTable],
                'source_item.' . SourceItemInterface::SOURCE_ID . ' = stock_source_link.' . StockSourceLink::SOURCE_ID,
                [StockSourceLink::STOCK_ID]
            )->where('source_item.source_item_id IN (?)', $sourceItemIds)
            ->group(['stock_source_link.' . StockSourceLink::STOCK_ID]);

        $items = $connection->fetchAll($select);
        return $this->getStockIdToSkuList($items);
    }

    /**
     * Return the assigned stock id to sku list.
     * @param array $items
     * @return SkuListInStockToUpdate[]
     */
    private function getStockIdToSkuList(array $items): array
    {
        $skuListInStockToUpdateList = [];
        foreach ($items as $item) {
            /** @var  SkuListInStockToUpdate $skuListInStockToUpdate */
            $skuListInStockToUpdate = $this->skuListInStockToUpdateFactory->create();
            $skuListInStockToUpdate->setStockId($item[StockSourceLink::STOCK_ID]);
            $skuListInStockToUpdate->setSkuList(explode(',', $item[SourceItemInterface::SKU]));
            $skuListInStockToUpdateList[] = $skuListInStockToUpdate;
        }
        return $skuListInStockToUpdateList;
    }
}
