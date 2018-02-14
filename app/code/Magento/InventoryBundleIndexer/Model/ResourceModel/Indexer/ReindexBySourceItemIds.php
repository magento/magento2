<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Model\ResourceModel\Indexer;

use ArrayIterator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexHandlerInterface;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\InventoryIndexer\Indexer\SourceItem\GetSkuListInStock;

/**
 * Check bundle children => if one of them in_stock - make bundle in_stock.
 */
class ReindexBySourceItemIds
{
    /**
     * @var GetSkuListInStock
     */
    private $getSkuListInStock;

    /**
     * @var GetBundlesIndexDataBySourceItemsSku
     */
    private $getBundlesIndexDataBySourceItemsSku;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexHandlerInterface
     */
    private $indexHandler;

    /**
     * @param GetBundlesIndexDataBySourceItemsSku $getBundlesIndexDataBySourceItemsSku
     * @param GetSkuListInStock $getSkuListInStock
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexHandlerInterface $indexHandler
     */
    public function __construct(
        GetBundlesIndexDataBySourceItemsSku $getBundlesIndexDataBySourceItemsSku,
        GetSkuListInStock $getSkuListInStock,
        IndexNameBuilder $indexNameBuilder,
        IndexHandlerInterface $indexHandler
    ) {
        $this->getBundlesIndexDataBySourceItemsSku = $getBundlesIndexDataBySourceItemsSku;
        $this->getSkuListInStock = $getSkuListInStock;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexHandler = $indexHandler;
    }

    /**
     * @param array $bundleSourceItemsIds
     * @return void
     */
    public function execute(array $bundleSourceItemsIds)
    {
        $skuListInStockList = $this->getSkuListInStock->execute($bundleSourceItemsIds);
        foreach ($skuListInStockList as $skuListInStock) {
            $stockId = $skuListInStock->getStockId();
            $bundlesIndexData = $this->getBundlesIndexDataBySourceItemsSku
                ->execute($skuListInStock->getSkuList(), $stockId);

            $mainIndexName = $this->indexNameBuilder
                ->setIndexId(InventoryIndexer::INDEXER_ID)
                ->addDimension('stock_', (string)$stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            $this->indexHandler->cleanIndex(
                $mainIndexName,
                new \ArrayIterator($skuListInStock->getSkuList()),
                ResourceConnection::DEFAULT_CONNECTION
            );

            $this->indexHandler->saveIndex(
                $mainIndexName,
                new ArrayIterator($bundlesIndexData),
                ResourceConnection::DEFAULT_CONNECTION
            );
        }
    }
}
