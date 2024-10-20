<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Order;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockProcessor;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ReturnProcessor
 *
 * @api
 * @since 100.0.0
 */
class ReturnProcessor
{
    /**
     * @var StockProcessor
     */
    private $stockIndexerProcessor;

    /**
     * ReturnProcessor constructor.
     * @param StockManagementInterface $stockManagement
     * @param StockProcessor $stockIndexer
     * @param PriceProcessor $priceIndexer
     * @param StoreManagerInterface $storeManager
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        private readonly StockManagementInterface $stockManagement,
        StockProcessor $stockIndexer,
        private readonly PriceProcessor $priceIndexer,
        private readonly StoreManagerInterface $storeManager,
        private readonly OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->stockIndexerProcessor = $stockIndexer;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param array $returnToStockItems
     * @param bool $isAutoReturn
     * @return void
     * @since 100.0.0
     */
    public function execute(
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        array $returnToStockItems = [],
        $isAutoReturn = false
    ) {
        $itemsToUpdate = [];
        foreach ($creditmemo->getItems() as $item) {
            $productId = $item->getProductId();
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            $parentItemId = $orderItem->getParentItemId();
            $qty = $item->getQty();
            if ($isAutoReturn || $this->canReturnItem($item, $qty, $parentItemId, $returnToStockItems)) {
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
     * @param CreditmemoItemInterface $item
     * @param int $qty
     * @param int[] $returnToStockItems
     * @param int $parentItemId
     * @return bool
     */
    private function canReturnItem(
        CreditmemoItemInterface $item,
        $qty,
        $parentItemId = null,
        array $returnToStockItems = []
    ) {
        return (in_array($item->getOrderItemId(), $returnToStockItems) || in_array($parentItemId, $returnToStockItems))
        && $qty;
    }
}
