<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesInventory\Observer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesInventory\Model\Order\ReturnProcessor;

/**
 * Catalog inventory module observer
 * @deprecated 100.2.0
 */
class RefundOrderInventoryObserver implements ObserverInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockManagementInterface
     */
    private $stockManagement;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    private $stockIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    private $priceIndexer;

    /**
     * @var \Magento\SalesInventory\Model\Order\ReturnProcessor
     */
    private $returnProcessor;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * RefundOrderInventoryObserver constructor.
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockManagementInterface $stockManagement
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     * @param ReturnProcessor $returnProcessor
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockManagementInterface $stockManagement,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Magento\SalesInventory\Model\Order\ReturnProcessor $returnProcessor,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockManagement = $stockManagement;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexer = $priceIndexer;
        $this->returnProcessor = $returnProcessor;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Return creditmemo items qty to stock
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
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
