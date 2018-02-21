<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexHandlerInterface;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

/**
 * Source Item indexer
 * Check bundle children, if one of them in_stock - make bundle in_stock
 *
 * @api
 */
class SourceItemIndexer
{
    /**
     * @var GetSkuListInStock
     */
    private $getSkuListInStock;

    /**
     * @var IndexDataProvider
     */
    private $indexDataProvider;

    /**
     * @var ChildrenSourceItemsIdsProvider
     */
    private $childrenSourceItemsIdsProvider;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexHandlerInterface
     */
    private $indexHandler;

    /**
     * @param GetSkuListInStock $getSkuListInStock
     * @param IndexDataProvider $indexDataProvider
     * @param ChildrenSourceItemsIdsProvider $childrenSourceItemsIdsProvider
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexHandlerInterface $indexHandler
     */
    public function __construct(
        GetSkuListInStock $getSkuListInStock,
        IndexDataProvider $indexDataProvider,
        ChildrenSourceItemsIdsProvider $childrenSourceItemsIdsProvider,
        IndexNameBuilder $indexNameBuilder,
        IndexHandlerInterface $indexHandler
    ) {
        $this->getSkuListInStock = $getSkuListInStock;
        $this->indexDataProvider = $indexDataProvider;
        $this->childrenSourceItemsIdsProvider = $childrenSourceItemsIdsProvider;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexHandler = $indexHandler;
    }

    /**
     * @return void
     */
    public function executeFull()
    {
        $bundleChildrenSourceItemsIdsWithSku = $this->childrenSourceItemsIdsProvider->execute();

        $this->executeByBundleChildrenSourceItemsIdsWithSku($bundleChildrenSourceItemsIdsWithSku);
    }

    /**
     * @param int $sourceItemId
     * @return void
     */
    public function executeRow(int $sourceItemId)
    {
        $this->executeList([$sourceItemId]);
    }

    /**
     * @param array $sourceItemIds
     * @return void
     */
    public function executeList(array $sourceItemIds)
    {
        $bundleChildrenSourceItemsIdsWithSku = $this->childrenSourceItemsIdsProvider->execute($sourceItemIds);

        $this->executeByBundleChildrenSourceItemsIdsWithSku($bundleChildrenSourceItemsIdsWithSku);
    }

    /**
     * @param array $bundleChildrenSourceItemsIdsWithSku
     * @return void
     */
    private function executeByBundleChildrenSourceItemsIdsWithSku(array $bundleChildrenSourceItemsIdsWithSku)
    {
        $skuListInStockList = $this->getSkuListInStock->execute($bundleChildrenSourceItemsIdsWithSku);
        foreach ($skuListInStockList as $skuListInStock) {
            $stockId = $skuListInStock->getStockId();
            $skuList = $skuListInStock->getSkuList();
            $bundleIndexData = $this->indexDataProvider->execute($skuList, $stockId);

            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(InventoryIndexer::INDEXER_ID)
                ->addDimension('stock_', (string)$stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            $this->indexHandler->cleanIndex(
                $mainIndexName,
                new \ArrayIterator(array_keys($skuList)),
                ResourceConnection::DEFAULT_CONNECTION
            );

            $this->indexHandler->saveIndex(
                $mainIndexName,
                $bundleIndexData,
                ResourceConnection::DEFAULT_CONNECTION
            );
        }
    }
}
