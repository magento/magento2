<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Order;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ReturnProcessor
 */
class ReturnProcessor
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
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * ReturnToStockPlugin constructor.
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockManagementInterface $stockManagement
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockManagementInterface $stockManagement,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        CreditmemoRepositoryInterface $creditmemoRepository,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockManagement = $stockManagement;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexer = $priceIndexer;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param array $returnToStockItems
     * @return void
     */
    public function execute(
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        array $returnToStockItems = []
    ) {
        if ($this->stockConfiguration->isAutoReturnEnabled()) {
            return;
        }
        $itemsToUpdate = [];

        /** @var CreditmemoItemInterface $item */
        foreach ($creditmemo->getItems() as $item) {
            $qty = $item->getQty();
            if ($this->canReturnItem($item, $returnToStockItems, $qty)) {
                $productId = $item->getProductId();
                $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
                $parentItemId = $orderItem->getParentItemId();
                /* @var $parentItem \Magento\Sales\Model\Order\Creditmemo\Item */
                $parentItem = $parentItemId ? $this->getItemByOrderId($creditmemo, $parentItemId) : false;
                $qty = $parentItem ? $parentItem->getQty() * $qty : $qty;
                if (isset($itemsToUpdate[$productId])) {
                    $itemsToUpdate[$productId] += $qty;
                } else {
                    $itemsToUpdate[$productId] = $qty;
                }
            }
        }

        if (!empty($itemsToUpdate)) {
            $store = $this->storeManager->getStore($order->getStoreId());
            foreach ($itemsToUpdate as $productId => $qty) {
                $this->stockManagement->backItemQty(
                    $productId,
                    $qty,
                    $store->getWebsiteId()
                );
            }

            $updatedItemIds = array_keys($itemsToUpdate);
            $this->stockIndexerProcessor->reindexList($updatedItemIds);
            $this->priceIndexer->reindexList($updatedItemIds);
        }
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param int $parentItemId
     * @return bool|CreditmemoItemInterface
     */
    private function getItemByOrderId(CreditmemoInterface $creditmemo, $parentItemId)
    {
        foreach ($creditmemo->getItems() as $item) {
            if ($item->getOrderItemId() == $parentItemId) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @param CreditmemoItemInterface $item
     * @param array $returnToStockItems
     * @param int $qty
     * @return bool
     */
    private function canReturnItem(CreditmemoItemInterface $item, array $returnToStockItems, $qty)
    {
        return in_array($item->getOrderItemId(), $returnToStockItems) && $qty;
    }
}
