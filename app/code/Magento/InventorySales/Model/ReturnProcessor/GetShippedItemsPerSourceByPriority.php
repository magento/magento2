<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\Framework\Exception\InputException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\GetSourceCodeByShipmentId;
use Magento\Framework\Exception\LocalizedException;

class GetShippedItemsPerSourceByPriority
{
    /**
     * @var GetSourceCodeByShipmentId
     */
    private $getSourceCodeByShipmentId;

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
     * @param GetSourceCodeByShipmentId $getSourceCodeByShipmentId
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     */
    public function __construct(
        GetSourceCodeByShipmentId $getSourceCodeByShipmentId,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
    ) {
        $this->getSourceCodeByShipmentId = $getSourceCodeByShipmentId;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
    }

    /**
     * @param OrderInterface $order
     * @param array $returnToStockItems
     * @return array
     * @throws InputException
     */
    public function execute(OrderInterface $order, array $returnToStockItems): array
    {
        $sources = [];
        /** @var Shipment $shipment */
        foreach ($order->getShipmentsCollection() as $shipment) {
            $sourceCode = $this->getSourceCodeByShipmentId->execute((int)$shipment->getId());
            foreach ($shipment->getItems() as $item) {
                if ($this->isValidItem($item->getOrderItem(), (float)$item->getQty(), $returnToStockItems)) {
                    $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                        [$item->getProductId()]
                    )[$item->getProductId()];
                    $sources[$sourceCode][$itemSku] = ($sources[$sourceCode][$itemSku] ?? 0) + $item->getQty();
                }
            }
        }
        $websiteId = $order->getStore()->getWebsiteId();

        // Sort items by source priority
        $sources = $this->sortSourcesByPriority($sources, (int)$websiteId);

        // Group items by SKU
        $sources = $this->groupItemsBySku($sources);

        return $sources;
    }

    /**
     * Group items by SKU
     *
     * @param array $sources
     * @return array
     */
    private function groupItemsBySku(array $sources): array
    {
        $skuPerSource = $itemsGroupedBySku = [];
        foreach ($sources as $sourceCode => $data) {
            foreach ($data as $sku => $qty) {
                if (empty($skuPerSource[$sku])) {
                    $itemsGroupedBySku[$sourceCode][$sku] = $qty;
                    $skuPerSource[$sku] = $sourceCode;
                } else {
                    $existingSourceCode = $skuPerSource[$sku];
                    $itemsGroupedBySku[$existingSourceCode][$sku] += $qty;
                }
            }
        }

        return $itemsGroupedBySku;
    }

    /**
     * Sort items by source priority
     *
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
     * @param OrderItemInterface $orderItem
     * @param float $qty
     * @param array $returnToStockItems
     * @return bool
     */
    private function isValidItem(OrderItemInterface $orderItem, float $qty, array $returnToStockItems): bool
    {
        $parentItemId = $orderItem->getParentItemId();

        return (in_array($orderItem->getId(), $returnToStockItems)
                || in_array($parentItemId, $returnToStockItems)) && $qty;
    }
}
