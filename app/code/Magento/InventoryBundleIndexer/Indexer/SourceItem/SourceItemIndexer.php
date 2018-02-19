<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

use ArrayIterator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexHandlerInterface;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\InventoryIndexer\Indexer\SourceItem\GetSkuListInStock;

/**
 * Source Item indexer
 * Check bundle children, if one of them in_stock - make bundle in_stock
 *
 * @api
 */
class SourceItemIndexer
{
    /**
     * @var GetAllBundleChildrenSourceItemsIdsWithSku
     */
    private $getAllBundleChildrenSourceItemsIdsWithSku;

    /**
     * @var GetSkuListInStock
     */
    private $getSkuListInStock;

    /**
     * @var GetBundlesIndexDataBySourceItemsSku
     */
    private $getBundlesIndexDataBySourceItemsSku;

    /**
     * @var GetBundleChildrenSourceItemsIdsWithSku
     */
    private $getBundleChildrenSourceItemsIdsBySku;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexHandlerInterface
     */
    private $indexHandler;

    /**
     * @param GetAllBundleChildrenSourceItemsIdsWithSku $getAllBundleChildrenSourceItemsIdsWithSku
     * @param GetSkuListInStock $getSkuListInStock
     * @param GetBundlesIndexDataBySourceItemsSku $getBundlesIndexDataBySourceItemsSku
     * @param GetBundleChildrenSourceItemsIdsWithSku $getBundleChildrenSourceItemsIdsBySku
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexHandlerInterface $indexHandler
     */
    public function __construct(
        GetAllBundleChildrenSourceItemsIdsWithSku $getAllBundleChildrenSourceItemsIdsWithSku,
        GetSkuListInStock $getSkuListInStock,
        GetBundlesIndexDataBySourceItemsSku $getBundlesIndexDataBySourceItemsSku,
        GetBundleChildrenSourceItemsIdsWithSku $getBundleChildrenSourceItemsIdsBySku,
        IndexNameBuilder $indexNameBuilder,
        IndexHandlerInterface $indexHandler
    ) {
        $this->getAllBundleChildrenSourceItemsIdsWithSku = $getAllBundleChildrenSourceItemsIdsWithSku;
        $this->getSkuListInStock = $getSkuListInStock;
        $this->getBundlesIndexDataBySourceItemsSku = $getBundlesIndexDataBySourceItemsSku;
        $this->getBundleChildrenSourceItemsIdsBySku = $getBundleChildrenSourceItemsIdsBySku;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexHandler = $indexHandler;
    }

    /**
     * @return void
     */
    public function executeFull()
    {
        $bundleChildrenSourceItemsIdsBySku = $this->getAllBundleChildrenSourceItemsIdsWithSku->execute();
        $this->executeList($bundleChildrenSourceItemsIdsBySku);
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
        $bundleChildrenSourceItemsIdsBySku = $this->getBundleChildrenSourceItemsIdsBySku->execute($sourceItemIds);

        foreach ($bundleChildrenSourceItemsIdsBySku as $bundleSku => $bundleChildrenSourceItemsIds) {
            $skuListInStockList = $this->getSkuListInStock->execute($bundleChildrenSourceItemsIds);
            foreach ($skuListInStockList as $skuListInStock) {
                $stockId = $skuListInStock->getStockId();
                $skuList = $skuListInStock->getSkuList();
                $bundlesIndexData = $this->getBundlesIndexDataBySourceItemsSku->execute($skuList, $stockId, $bundleSku);

                $mainIndexName = $this->indexNameBuilder
                    ->setIndexId(InventoryIndexer::INDEXER_ID)
                    ->addDimension('stock_', (string)$stockId)
                    ->setAlias(Alias::ALIAS_MAIN)
                    ->build();

                $this->indexHandler->cleanIndex(
                    $mainIndexName,
                    new \ArrayIterator([$bundleSku]),
                    ResourceConnection::DEFAULT_CONNECTION
                );

                $this->indexHandler->saveIndex(
                    $mainIndexName,
                    new ArrayIterator([$bundlesIndexData]),
                    ResourceConnection::DEFAULT_CONNECTION
                );
            }
        }
    }
}
