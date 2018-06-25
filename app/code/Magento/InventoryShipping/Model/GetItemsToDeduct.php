<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventoryShipping\Model\SourceDeduction\Request\ItemToDeductInterfaceFactory;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Shipment\Item;

class GetItemsToDeduct
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
     * @param Item $shipmentItem
     * @return array
     * @throws NoSuchEntityException
     */
    public function execute(Item $shipmentItem): array
    {
        $orderItem = $shipmentItem->getOrderItem();
        $itemsToShip = [];
        if ($orderItem->getHasChildren()) {
            if ($orderItem->isDummy(true)) {
                return [];
            }
            $itemsToShip = $this->processComplexItem($shipmentItem);
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

        return $itemsToShip;
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
