<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\Framework\Serialize\Serializer\Json;

class GetItemsToCancelFromOrderItem
{
    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItem;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param Json $jsonSerializer
     */
    public function __construct(
        GetSkuFromOrderItemInterface $getSkuFromOrderItem,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        Json $jsonSerializer
    ) {
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param OrderItem $orderItem
     * @return array
     */
    public function execute(OrderItem $orderItem): array
    {
        $itemsToSell = [];
        if ($orderItem->getHasChildren()) {
            if (!$orderItem->isDummy(true)) {
                foreach ($this->processComplexItem($orderItem) as $item) {
                    $itemsToSell[] = $item;
                }
            }
        } elseif (!$orderItem->isDummy(true)) {
            $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
            $qtyToCancel = $orderItem->getIsVirtual() ? $this->getQtyToCancelForVirtualItem($orderItem)
                : $this->getQtyToCancelForPhysicalItem($orderItem);
            $itemsToSell[] = $this->itemsToSellFactory->create([
                'sku' => $itemSku,
                'qty' => $qtyToCancel
            ]);
        }

        return $itemsToSell;
    }

    /**
     * @param OrderItem $orderItem
     * @return array
     */
    private function processComplexItem(OrderItem $orderItem): array
    {
        $itemsToShip = [];
        foreach ($orderItem->getChildrenItems() as $item) {
            $productOptions = $item->getProductOptions();
            if (isset($productOptions['bundle_selection_attributes'])) {
                $bundleSelectionAttributes = $this->jsonSerializer->unserialize(
                    $productOptions['bundle_selection_attributes']
                );
                if ($bundleSelectionAttributes) {
                    $qtyToCancel = $orderItem->getIsVirtual() ? $this->getQtyToCancelForVirtualItem($orderItem)
                        : $this->getQtyToCancelForPhysicalItem($orderItem);
                    $qty = $bundleSelectionAttributes['qty'] * $qtyToCancel;
                    $itemSku = $this->getSkuFromOrderItem->execute($item);
                    $itemsToShip[] = $this->itemsToSellFactory->create([
                        'sku' => $itemSku,
                        'qty' => $qty
                    ]);
                }
            } else {
                // configurable product
                $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
                $qtyToCancel = $orderItem->getIsVirtual() ? $this->getQtyToCancelForVirtualItem($orderItem)
                    : $this->getQtyToCancelForPhysicalItem($orderItem);
                $itemsToShip[] = $this->itemsToSellFactory->create([
                    'sku' => $itemSku,
                    'qty' => $qtyToCancel
                ]);
            }
        }

        return $itemsToShip;
    }

    /**
     * @param OrderItem $item
     * @return float
     */
    private function getQtyToCancelForPhysicalItem(OrderItem $item): float
    {
        return $item->getQtyOrdered() - $item->getQtyShipped() - $item->getQtyCanceled();
    }

    /**
     * @param OrderItem $item
     * @return float
     */
    private function getQtyToCancelForVirtualItem(OrderItem $item): float
    {
        return $item->getQtyOrdered() - $item->getQtyInvoiced() - $item->getQtyCanceled();
    }
}
