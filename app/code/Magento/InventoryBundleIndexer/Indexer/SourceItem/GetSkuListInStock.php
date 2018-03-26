<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryIndexer\Indexer\SourceItem\SkuListInStock;
use Magento\InventoryIndexer\Indexer\SourceItem\SkuListInStockFactory;

/**
 * Returns relations between stock and sku list for bundle products.
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
     * @var int
     */
    private $groupConcatMaxLen;

    /**
     * GetSkuListInStock constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param SkuListInStockFactory $skuListInStockFactory
     * @param int $groupConcatMaxLen
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SkuListInStockFactory $skuListInStockFactory,
        int $groupConcatMaxLen
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->skuListInStockFactory = $skuListInStockFactory;
        $this->groupConcatMaxLen = $groupConcatMaxLen;
    }

    /**
     * Returns all assigned Stock ids by given Source Item ids.
     *
     * @param array $bundleChildrenSourceItemsIds
     *
     * @return SkuListInStock[]
     */
    public function execute(array $bundleChildrenSourceItemsIds): array
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
                        sprintf("GROUP_CONCAT(DISTINCT %s SEPARATOR ',')", 'source_item.' . SourceItemInterface::SKU),
                    SourceItem::ID_FIELD_NAME =>
                        sprintf("GROUP_CONCAT(DISTINCT %s SEPARATOR ',')", 'source_item.' . SourceItem::ID_FIELD_NAME)
                ]
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
                $bundleChildrenSourceItemsIds
            )
            ->group(['stock_source_link.' . StockSourceLink::STOCK_ID]);

        $connection->query('SET group_concat_max_len = ' . $this->groupConcatMaxLen);
        $items = $connection->fetchAll($select);

        return $this->getStockIdToSkuList($items, $bundleChildrenSourceItemsIds);
    }

    /**
     * Return the assigned stock id to sku list;
     * Sku list format: [bundle sku => [children skus]].
     *
     * @param array $items
     * @param array $bundleChildrenSourceItemsIds
     *
     * @return SkuListInStock[]
     */
    private function getStockIdToSkuList(array $items, array $bundleChildrenSourceItemsIds): array
    {
        $skuListInStockList = [];
        foreach ($items as $item) {
            $skus = [];
            $sourceItemsIdsWithSkus = array_combine(
                explode(',', $item[SourceItem::ID_FIELD_NAME]),
                explode(',', $item[SourceItemInterface::SKU])
            );
            foreach ($bundleChildrenSourceItemsIds as $bundleSku => $sourceItemIds) {
                foreach ($sourceItemIds as $sourceItemId) {
                    if (isset($sourceItemsIdsWithSkus[$sourceItemId])) {
                        $skus[$bundleSku][] = $sourceItemsIdsWithSkus[$sourceItemId];
                    }
                }
            }
            /** @var SkuListInStock $skuListInStock */
            $skuListInStock = $this->skuListInStockFactory->create();
            $skuListInStock->setStockId((int)$item[StockSourceLink::STOCK_ID]);
            $skuListInStock->setSkuList($skus);
            $skuListInStockList[] = $skuListInStock;
        }

        return $skuListInStockList;
    }
}
