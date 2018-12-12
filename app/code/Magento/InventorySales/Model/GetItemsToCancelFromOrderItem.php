<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
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
     * @return ItemToSellInterface[]
     */
    public function execute(OrderItem $orderItem): array
    {
        $itemsToCancel = [];
        if ($orderItem->getHasChildren()) {
            if (!$orderItem->isDummy(true)) {
                foreach ($this->processComplexItem($orderItem) as $item) {
                    $itemsToCancel[] = $item;
                }
            }
        } elseif (!$orderItem->isDummy(true)) {
            $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
            $itemsToCancel[] = $this->itemsToSellFactory->create([
                'sku' => $itemSku,
                'qty' => $this->getQtyToCancel($orderItem)
            ]);
        }

        return $this->groupItemsBySku($itemsToCancel);
    }

    /**
     * @param ItemToSellInterface[] $itemsToCancel
     * @return ItemToSellInterface[]
     */
    private function groupItemsBySku(array $itemsToCancel): array
    {
        $processingItems = $groupedItems = [];
        foreach ($itemsToCancel as $item) {
            if ($item->getQuantity() == 0) {
                continue;
            }
            if (empty($processingItems[$item->getSku()])) {
                $processingItems[$item->getSku()] = $item->getQuantity();
            } else {
                $processingItems[$item->getSku()] += $item->getQuantity();
            }
        }

        foreach ($processingItems as $sku => $qty) {
            $groupedItems[] = $this->itemsToSellFactory->create([
                'sku' => $sku,
                'qty' => $qty
            ]);
        }

        return $groupedItems;
    }

    /**
     * @param OrderItem $orderItem
     * @return ItemToSellInterface[]
     */
    private function processComplexItem(OrderItem $orderItem): array
    {
        $itemsToCancel = [];
        foreach ($orderItem->getChildrenItems() as $item) {
            $productOptions = $item->getProductOptions();
            if (isset($productOptions['bundle_selection_attributes'])) {
                $bundleSelectionAttributes = $this->jsonSerializer->unserialize(
                    $productOptions['bundle_selection_attributes']
                );
                if ($bundleSelectionAttributes) {
                    $shippedQty = $bundleSelectionAttributes['qty'] * $orderItem->getQtyShipped();
                    $qty = $item->getQtyOrdered() - max($shippedQty, $item->getQtyInvoiced()) - $item->getQtyCanceled();
                    $itemSku = $this->getSkuFromOrderItem->execute($item);
                    $itemsToCancel[] = $this->itemsToSellFactory->create([
                        'sku' => $itemSku,
                        'qty' => $qty
                    ]);
                }
            } else {
                // configurable product
                $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
                $itemsToCancel[] = $this->itemsToSellFactory->create([
                    'sku' => $itemSku,
                    'qty' => $this->getQtyToCancel($orderItem)
                ]);
            }
        }
        return $itemsToCancel;
    }

    /**
     * @param OrderItem $item
     * @return float
     */
    private function getQtyToCancel(OrderItem $item): float
    {
        return $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
    }
}
