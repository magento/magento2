<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\SalesInventory;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\GetSourceCodeByShipmentId;
use Magento\Sales\Model\Order\Shipment;
use Magento\InventoryShipping\Model\GetSourceItemBySourceCodeAndSku;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\SalesInventory\Model\Order\ReturnProcessor;

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
     * @var array
     */
    private $returnToStockItems = [];

    /**
     * @param GetSourceCodeByShipmentId $getSourceCodeByShipmentId
     * @param GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     */
    public function __construct(
        GetSourceCodeByShipmentId $getSourceCodeByShipmentId,
        GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku,
        SourceItemsSaveInterface $sourceItemsSave,
        GetSkusByProductIdsInterface $getSkusByProductIds
    ) {
        $this->getSourceCodeByShipmentId = $getSourceCodeByShipmentId;
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->getSkusByProductIds = $getSkusByProductIds;
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
            if ($this->isValidItem($orderItem->getId(), $qty, $parentItemId) && empty($parentItemId)) {
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
            $qtyBackToSource = $qty;
            foreach ($shippedItems as $sourceCode => $data) {
                if (empty($processedItems[$sku])) {
                    continue;
                }

                $availableQtyToBack = $data[$sku] + $processedItems[$sku] + $qty;
                $backQty = min($availableQtyToBack, $qtyBackToSource);

                // check if source has some qty of SKU, so it's possible to take them into account
                if ($this->isZero((float)$availableQtyToBack)) {
                    continue;
                }
                $qtyBackToSource -= $backQty;

                if ($backQty > 0) {
                    $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute($sourceCode, $sku);
                    $sourceItem->setQuantity($sourceItem->getQuantity() + $backQty);
                    $sourceItemToSave[] = $sourceItem;
                }
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

        return $sources;
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
