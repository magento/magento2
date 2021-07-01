<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockProcessor;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Reindex quote inventory
 */
class ReindexQuoteInventoryObserver implements ObserverInterface
{
    /**
     * @var StockProcessor
     */
    private $stockIndexerProcessor;

    /**
     * @var PriceProcessor
     */
    private $priceIndexer;

    /**
     * @var ItemsForReindex
     */
    private $itemsForReindex;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StockProcessor $stockIndexerProcessor
     * @param PriceProcessor $priceIndexer
     * @param ItemsForReindex $itemsForReindex
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockProcessor $stockIndexerProcessor,
        PriceProcessor $priceIndexer,
        ItemsForReindex $itemsForReindex,
        LoggerInterface $logger
    ) {
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexer = $priceIndexer;
        $this->itemsForReindex = $itemsForReindex;
        $this->logger = $logger;
    }

    /**
     * Refresh stock index for specific stock items after successful order placement
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();
        $productIds = [];
        foreach ($quote->getAllItems() as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }

        if ($productIds) {
            try {
                $this->stockIndexerProcessor->reindexList($productIds);
            } catch (\Exception $exception) {
                $this->logger->error($exception);
            }
        }

        // Reindex previously remembered items
        $productIds = [];
        foreach ($this->itemsForReindex->getItems() as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }

        if (!empty($productIds)) {
            $this->priceIndexer->reindexList($productIds);
        }

        $this->itemsForReindex->clear();
        // Clear list of remembered items - we don't need it anymore
    }
}
