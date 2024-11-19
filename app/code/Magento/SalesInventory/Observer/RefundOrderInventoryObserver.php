<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesInventory\Observer;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockProcessor;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo as OrderCreditmemo;
use Magento\SalesInventory\Model\Order\ReturnProcessor;

/**
 * Catalog inventory module observer
 * @deprecated 100.2.0
 */
class RefundOrderInventoryObserver implements ObserverInterface
{
    /**
     * RefundOrderInventoryObserver constructor.
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockManagementInterface $stockManagement
     * @param StockProcessor $stockIndexerProcessor
     * @param PriceProcessor $priceIndexer
     * @param ReturnProcessor $returnProcessor
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        private readonly StockConfigurationInterface $stockConfiguration,
        private readonly StockManagementInterface $stockManagement,
        private readonly StockProcessor $stockIndexerProcessor,
        private readonly PriceProcessor $priceIndexer,
        private readonly ReturnProcessor $returnProcessor,
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * Return creditmemo items qty to stock
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /* @var OrderCreditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $this->orderRepository->get($creditmemo->getOrderId());
        $returnToStockItems = [];
        foreach ($creditmemo->getItems() as $item) {
            if ($item->getBackToStock()) {
                $returnToStockItems[] = $item->getOrderItemId();
            }
        }
        if (!empty($returnToStockItems)) {
            $this->returnProcessor->execute($creditmemo, $order, $returnToStockItems);
        }
    }
}
