<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Catalog inventory module observer
 */
class RevertQuoteInventoryObserver implements ObserverInterface
{
    /**
     * @var ProductQty
     */
    protected $productQty;

    /**
     * @var StockManagementInterface
     */
    protected $stockManagement;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $stockIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $priceIndexer;

    /**
     * RevertQuoteInventory constructor.
     * @param ProductQty $productQty
     * @param StockManagementInterface $stockManagement
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     */
    public function __construct(
        ProductQty $productQty,
        StockManagementInterface $stockManagement,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
    ) {
        $this->productQty = $productQty;
        $this->stockManagement = $stockManagement;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexer = $priceIndexer;
    }

    /**
     * Revert quote items inventory data (cover not success order place case)
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $items = $this->productQty->getProductQty($quote->getAllItems());
        $this->stockManagement->revertProductsSale($items, $quote->getStore()->getWebsiteId());
        $productIds = array_keys($items);
        if (!empty($productIds)) {
            $this->stockIndexerProcessor->reindexList($productIds);
            $this->priceIndexer->reindexList($productIds);
        }
        // Clear flag, so if order placement retried again with success - it will be processed
        $quote->setInventoryProcessed(false);
    }
}
