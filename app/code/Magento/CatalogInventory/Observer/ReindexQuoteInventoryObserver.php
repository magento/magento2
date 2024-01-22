<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogInventory\Observer;

use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor  as PriceProcessor;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Responsible for re-indexing stock items after a successful order.
 */
class ReindexQuoteInventoryObserver implements ObserverInterface
{
    /**
     * @var StockProcessor
     */
    private StockProcessor $stockIndexerProcessor;

    /**
     * @var PriceProcessor
     */
    private PriceProcessor $priceIndexer;

    /**
     * @var ItemsForReindex
     */
    private ItemsForReindex $itemsForReindex;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

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
    public function execute(EventObserver $observer): void
    {
        try {
            // Reindex quote ids
            $quote = $observer->getEvent()->getData('quote');
            $productIds = [];
            foreach ($quote->getAllItems() as $item) {
                $productIds[$item->getData('product_id')] = $item->getData('product_id');
                $children = $item->getData('children_items');
                if ($children) {
                    foreach ($children as $childItem) {
                        $productIds[$childItem->getData('product_id')] = $childItem->getData('product_id');
                    }
                }
            }

            if ($productIds) {
                $this->stockIndexerProcessor->reindexList($productIds);
            }

            // Reindex previously remembered items
            $productIds = [];
            foreach ($this->itemsForReindex->getItems() as $item) {
                $item->save();
                $productIds[] = $item->getData('product_id');
            }

            if (!empty($productIds)) {
                $this->priceIndexer->reindexList($productIds);
            }

            $this->itemsForReindex->clear();
            // Clear list of remembered items - we don't need it anymore
        } catch (LocalizedException $exception) {
            $this->logger->error('Error while re-indexing order items: ' . $exception->getLogMessage());
            $this->stockIndexerProcessor->markIndexerAsInvalid();
            $this->priceIndexer->markIndexerAsInvalid();
        }
    }
}
