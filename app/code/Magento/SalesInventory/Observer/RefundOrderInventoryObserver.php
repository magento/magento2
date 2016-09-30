<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesInventory\Observer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\SalesInventory\Model\Order\ReturnProcessor;

/**
 * Catalog inventory module observer
 * @deprecated
 */
class RefundOrderInventoryObserver implements ObserverInterface
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

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
     * @var \Magento\SalesInventory\Model\Order\ReturnProcessor
     */
    private $returnProcessor;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockManagementInterface $stockManagement
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockManagementInterface $stockManagement,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockManagement = $stockManagement;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexer = $priceIndexer;
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
        $order = $this->getOrderRepository()->get($creditmemo->getOrderId());
        $returnToStockItems = [];
        foreach ($creditmemo->getItems() as $item) {
            if ($item->getBackToStock()) {
                $returnToStockItems[] = $item->getOrderItemId();
            }
        }
        $this->getReturnProcessor()->execute(
            $creditmemo,
            $order,
            $returnToStockItems,
            $this->stockConfiguration->isAutoReturnEnabled()
        );
    }

    /**
     * Get OrderRepository
     *
     * @return OrderRepository
     * @deprecated
     */
    private function getOrderRepository()
    {
        if (!$this->orderRepository) {
            $this->orderRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(OrderRepository::class);

        }
        return $this->orderRepository;
    }

    /**
     * Get OrderRepository
     *
     * @return ReturnProcessor
     * @deprecated
     */
    private function getReturnProcessor()
    {
        if (!$this->returnProcessor) {
            $this->returnProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(ReturnProcessor::class);
        }
        return $this->returnProcessor;
    }
}
