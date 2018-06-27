<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Sales\Model\Order\Shipment\Item;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventoryShipping\Model\SourceDeduction\Request\ItemToDeductInterfaceFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Framework\Exception\NoSuchEntityException;

class GetItemsToDeductFromShipment
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var ItemToDeductInterfaceFactory
     */
    private $itemToDeduct;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param Json $jsonSerializer
     * @param ItemToDeductInterfaceFactory $itemToDeduct
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        Json $jsonSerializer,
        ItemToDeductInterfaceFactory $itemToDeduct
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->jsonSerializer = $jsonSerializer;
        $this->itemToDeduct = $itemToDeduct;
    }

    /**
     * @param Shipment $shipment
     * @return array
     * @throws NoSuchEntityException
     */
    public function execute(Shipment $shipment): array
    {
        $itemsToShip = [];

        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
        foreach ($shipment->getAllItems() as $shipmentItem) {
            $orderItem = $shipmentItem->getOrderItem();
            if ($orderItem->getHasChildren()) {
                if (!$orderItem->isDummy(true)) {
                    foreach ($this->processComplexItem($shipmentItem) as $item) {
                        $itemsToShip[] = $item;
                    }
                }
            } else {
                $itemSku = $shipmentItem->getSku() ?: $this->getSkusByProductIds->execute(
                    [$shipmentItem->getProductId()]
                )[$shipmentItem->getProductId()];
                $qty = $this->castQty($orderItem, $shipmentItem->getQty());
                $itemsToShip[] = $this->itemToDeduct->create([
                    'sku' => $itemSku,
                    'qty' => $qty
                ]);
            }
        }

        return $this->groupItemsBySku($itemsToShip);
    }

    /**
     * @param array $shipmentItems
     * @return array
     */
    private function groupItemsBySku(array $shipmentItems): array
    {
        $processingItems = $groupedItems = [];
        foreach ($shipmentItems as $shipmentItem) {
            if (empty($processingItems[$shipmentItem->getSku()])) {
                $processingItems[$shipmentItem->getSku()] = $shipmentItem->getQty();
            } else {
                $processingItems[$shipmentItem->getSku()] += $shipmentItem->getQty();
            }
        }

        foreach ($processingItems as $sku => $qty) {
            $groupedItems[] = $this->itemToDeduct->create([
                'sku' => $sku,
                'qty' => $qty
            ]);
        }

        return $groupedItems;
    }

    /**
     * @param Item $shipmentItem
     * @return array
     * @throws NoSuchEntityException
     */
    private function processComplexItem(Item $shipmentItem): array
    {
        $orderItem = $shipmentItem->getOrderItem();
        $itemsToShip = [];
        foreach ($orderItem->getChildrenItems() as $item) {
            if ($item->getIsVirtual() || $item->getLockedDoShip()) {
                continue;
            }
            $productOptions = $item->getProductOptions();
            if (isset($productOptions['bundle_selection_attributes'])) {
                $bundleSelectionAttributes = $this->jsonSerializer->unserialize(
                    $productOptions['bundle_selection_attributes']
                );
                if ($bundleSelectionAttributes) {
                    $qty = $bundleSelectionAttributes['qty'] * $shipmentItem->getQty();
                    $qty = $this->castQty($item, $qty);
                    $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                        [$item->getProductId()]
                    )[$item->getProductId()];
                    $itemsToShip[] = $this->itemToDeduct->create([
                        'sku' => $itemSku,
                        'qty' => $qty
                    ]);
                    continue;
                }
            } else {
                // configurable product
                $itemSku = $shipmentItem->getSku() ?: $this->getSkusByProductIds->execute(
                    [$shipmentItem->getProductId()]
                )[$shipmentItem->getProductId()];
                $qty = $this->castQty($orderItem, $shipmentItem->getQty());
                $itemsToShip[] = $this->itemToDeduct->create([
                    'sku' => $itemSku,
                    'qty' => $qty
                ]);
            }
        }

        return $itemsToShip;
    }

    /**
     * @param OrderItem $item
     * @param string|int|float $qty
     * @return float|int
     */
    private function castQty(OrderItem $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }
}
