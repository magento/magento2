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
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
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
     * @var array
     */
    private $returnToStockItems = [];

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
        $this->returnToStockItems = $returnToStockItems;
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
}
