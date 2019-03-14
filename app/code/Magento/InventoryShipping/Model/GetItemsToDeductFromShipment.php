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
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductInterface;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductInterfaceFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Framework\Exception\NoSuchEntityException;

class GetItemsToDeductFromShipment
{
    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItem;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var ItemToDeductInterfaceFactory
     */
    private $itemToDeduct;

    /**
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param Json $jsonSerializer
     * @param ItemToDeductInterfaceFactory $itemToDeduct
     */
    public function __construct(
        GetSkuFromOrderItemInterface $getSkuFromOrderItem,
        Json $jsonSerializer,
        ItemToDeductInterfaceFactory $itemToDeduct
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->itemToDeduct = $itemToDeduct;
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
    }

    /**
     * @param Shipment $shipment
     * @return ItemToDeductInterface[]
     * @throws NoSuchEntityException
     */
    public function execute(Shipment $shipment): array
    {
        $itemsToShip = [];

        /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
        foreach ($shipment->getAllItems() as $shipmentItem) {
            $orderItem = $shipmentItem->getOrderItem();
            // This code was added as quick fix for merge mainline
            // https://github.com/magento-engcom/msi/issues/1586
            if (null === $orderItem) {
                continue;
            }
            if ($orderItem->getHasChildren()) {
                if (!$orderItem->isDummy(true)) {
                    foreach ($this->processComplexItem($shipmentItem) as $item) {
                        $itemsToShip[] = $item;
                    }
                }
            } else {
                $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
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
                    $itemSku = $this->getSkuFromOrderItem->execute($item);
                    $itemsToShip[] = $this->itemToDeduct->create([
                        'sku' => $itemSku,
                        'qty' => $qty
                    ]);
                    continue;
                }
            } else {
                // configurable product
                $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
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
