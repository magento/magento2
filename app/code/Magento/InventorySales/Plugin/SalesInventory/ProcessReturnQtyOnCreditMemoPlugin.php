<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\SalesInventory;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\GetSourceCodeByShipmentId;
use Magento\Sales\Model\Order\Shipment;
use Magento\InventoryShipping\Model\GetSourceItemBySourceCodeAndSku;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\SalesInventory\Model\Order\ReturnProcessor;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;

class ProcessReturnQtyOnCreditMemoPlugin
{
    /**
     * @var GetSourceCodeByShipmentId
     */
    private $getSourceCodeByShipmentId;

    /**
     * @var GetSourceItemBySourceCodeAndSku
     */
    private $getSourceItemBySourceCodeAndSku;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * @var array
     */
    private $returnToStockItems = [];

    /**
     * @param GetSourceCodeByShipmentId $getSourceCodeByShipmentId
     * @param GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     */
    public function __construct(
        GetSourceCodeByShipmentId $getSourceCodeByShipmentId,
        GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku,
        SourceItemsSaveInterface $sourceItemsSave,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
    ) {
        $this->getSourceCodeByShipmentId = $getSourceCodeByShipmentId;
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
    }

    /**
     * @param ReturnProcessor $subject
     * @param callable $proceed
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param array $returnToStockItems
     * @param bool $isAutoReturn
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        ReturnProcessor $subject,
        callable $proceed,
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        array $returnToStockItems = [],
        $isAutoReturn = false
    ) {
        $itemsToRefund = $processedItems = [];
        $this->returnToStockItems = $returnToStockItems;
        foreach ($creditmemo->getItems() as $item) {
            $orderItem = $item->getOrderItem();
            $parentItemId = $orderItem->getParentItemId();
            $qty = (float)$item->getQty();
            if ($this->isValidItem($orderItem->getId(), $qty, $parentItemId) && !$orderItem->isDummy()) {
                $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                    [$item->getProductId()]
                )[$item->getProductId()];
                $itemsToRefund[$itemSku] = ($itemsToRefund[$itemSku] ?? 0) + $qty;
                $processedQty = $orderItem->getQtyCanceled() - $orderItem->getQtyRefunded();
                $processedItems[$itemSku] = ($processedItems[$itemSku] ?? 0) + (float)$processedQty;
            }
        }

        $shippedItems = $this->getShippedItemsPerSource($order);

        $this->processItems($itemsToRefund, $processedItems, $shippedItems);
    }

    /**
     * @param array $itemsToRefund
     * @param array $processedItems
     * @param array $shippedItems
     */
    private function processItems(array $itemsToRefund, array $processedItems, array $shippedItems)
    {
        $sourceItemToSave = [];
        foreach ($itemsToRefund as $sku => $qty) {
            if (empty($processedItems[$sku])) {
                continue;
            }

            $qtyBackToSource = $qty;
            $originalProcessedQty = $processedItems[$sku] + $qty;

            foreach ($shippedItems as $sourceCode => $data) {
                if (empty($data[$sku])) {
                    continue;
                }

                $availableQtyToBack = $data[$sku] + $originalProcessedQty;
                $backQty = min($availableQtyToBack, $qtyBackToSource);
                $originalProcessedQty += $data[$sku];

                // check if source has some qty of SKU, so it's possible to take them into account
                if ($this->isZero((float)$availableQtyToBack)) {
                    continue;
                }

                if ($backQty > 0) {
                    $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute($sourceCode, $sku);
                    $sourceItem->setQuantity($sourceItem->getQuantity() + $backQty);
                    $sourceItemToSave[] = $sourceItem;
                }

                $qtyBackToSource -= $backQty;
            }
        }

        if (!empty($sourceItemToSave)) {
            $this->sourceItemsSave->execute($sourceItemToSave);
        }
    }

    /**
     * @param OrderInterface|Order $order
     * @return array
     */
    private function getShippedItemsPerSource(OrderInterface $order): array
    {
        $sources = [];
        /** @var Shipment $shipment */
        foreach ($order->getShipmentsCollection() as $shipment) {
            $sourceCode = $this->getSourceCodeByShipmentId->execute((int)$shipment->getId());
            foreach ($shipment->getItems() as $item) {
                $orderItem = $item->getOrderItem();
                $parentItemId = $orderItem->getParentItemId();
                if ($this->isValidItem($orderItem->getId(), $item->getQty(), $parentItemId)) {
                    $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                        [$item->getProductId()]
                    )[$item->getProductId()];
                    $sources[$sourceCode][$itemSku] = ($sources[$sourceCode][$itemSku] ?? 0) + $item->getQty();
                }
            }
        }
        $websiteId = $order->getStore()->getWebsiteId();

        return $this->sortSourcesByPriority($sources, (int)$websiteId);
    }

    /**
     * @param array $sources
     * @param int $websiteId
     * @return array
     */
    private function sortSourcesByPriority(array $sources, int $websiteId): array
    {
        $sourcesByPriority = [];
        try {
            $stockId = (int)$this->stockByWebsiteIdResolver->get($websiteId)->getStockId();
            $assignedSourcesToStock = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
        } catch (LocalizedException $e) {
            $assignedSourcesToStock = [];
        }

        foreach ($assignedSourcesToStock as $source) {
            if (!empty($sources[$source->getSourceCode()])) {
                $sourcesByPriority[$source->getSourceCode()] = $sources[$source->getSourceCode()];
                unset($sources[$source->getSourceCode()]);
            }
        }

        foreach ($sources as $sourceCode => $data) {
            $sourcesByPriority[$sourceCode] = $data;
        }

        return $sourcesByPriority;
    }

    /**
     * @param int $orderItemId
     * @param int $qty
     * @param int $parentItemId
     * @return bool
     */
    private function isValidItem($orderItemId, $qty, $parentItemId = null): bool
    {
        return (in_array($orderItemId, $this->returnToStockItems)
                || in_array($parentItemId, $this->returnToStockItems)) && $qty;
    }

    /**
     * Compare float number with some epsilon
     *
     * @param float $floatNumber
     *
     * @return bool
     */
    private function isZero(float $floatNumber): bool
    {
        return $floatNumber < 0.0000001;
    }
}
