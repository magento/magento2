<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Returns relations between stock and sku list
 */
class GetSkuListInStock
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SkuListInStockFactory
     */
    private $skuListInStockFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SkuListInStockFactory $skuListInStockFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SkuListInStockFactory $skuListInStockFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->skuListInStockFactory = $skuListInStockFactory;
    }

    /**
     * Returns all assigned Stock ids by given Source Item ids
     *
     * @param int[] $sourceItemIds
     * @return SkuListInStock[] List of stock id to sku1,sku2 assignment
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
        $items = [];

        $select = $connection
            ->select()
            ->from(
                ['source_item' => $sourceItemTable],
                [SourceItemInterface::SKU => 'source_item.' . SourceItemInterface::SKU]
            )->joinInner(
                ['stock_source_link' => $sourceStockLinkTable],
                sprintf(
                    'source_item.%s = stock_source_link.%s',
                    SourceItemInterface::SOURCE_CODE,
                    StockSourceLink::SOURCE_CODE
                ),
                [StockSourceLink::STOCK_ID]
            )->where(
                'source_item.source_item_id IN (?)',
                $sourceItemIds
            );

        $dbStatement = $connection->query($select);
        while ($item = $dbStatement->fetch()) {
            $items[$item[StockSourceLink::STOCK_ID]][$item[SourceItemInterface::SKU]] = $item[SourceItemInterface::SKU];
        }

        return $this->getStockIdToSkuList($items);
    }

    /**
     * Return the assigned stock id to sku list
     *
     * @param array $items
     * @return SkuListInStock[]
     */
    private function getStockIdToSkuList(array $items): array
    {
        $skuListInStockList = [];
        foreach ($items as $stockId => $skuList) {
            /** @var SkuListInStock $skuListInStock */
            $skuListInStock = $this->skuListInStockFactory->create();
            $skuListInStock->setStockId((int)$stockId);
            $skuListInStock->setSkuList($skuList);
            $skuListInStockList[] = $skuListInStock;
        }
        return $skuListInStockList;
    }
}
