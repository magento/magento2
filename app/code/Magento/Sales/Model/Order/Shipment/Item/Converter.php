<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Shipment\Item;

use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;

/**
 * Converts ShipmentItemCreationInterface data objects
 *
 * @api
 */
class Converter
{
    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    private $itemFactory;

    /**
     * @param ShipmentItemCreationInterfaceFactory $itemFactory
     */
    public function __construct(
        ShipmentItemCreationInterfaceFactory $itemFactory
    ) {
        $this->itemFactory = $itemFactory;
    }

    /**
     * Converts ShipmentItemCreationInterface array to Id => Quantity array. Initializes from order if array is empty.
     *
     * @param ShipmentItemCreationInterface[] $items
     * @param OrderInterface $order
     * @return array
     */
    public function convertItemCreationToQuantityArray(array $items, OrderInterface $order)
    {
        $shipmentItems = [];
        if (empty($items)) {
            /** @var OrderItemInterface $item */
            foreach ($order->getItems() as $item) {
                if (!$item->getIsVirtual() && !$item->getParentItem()) {
                    $shipmentItems[$item->getItemId()] = $item->getQtyOrdered();
                }
            }
        } else {
            /** @var ShipmentItemCreationInterface $item */
            foreach ($items as $item) {
                $shipmentItems[$item->getOrderItemId()] = $item->getQty();
            }
        }

        return $shipmentItems;
    }

    /**
     * Converts array of Id => Quantity to ShipmentItemCreationInterface array.
     *
     * @param array $items
     * @return ShipmentItemCreationInterface[]
     */
    public function convertQuantityArrayToItemCreation(array $items)
    {
        $shipmentItems = [];
        foreach ($items as $itemId => $quantity) {
            /** @var ShipmentItemCreationInterface $item */
            $item = $this->itemFactory->create();
            $item->setOrderItemId($itemId);
            $item->setQty($quantity);
            $shipmentItems[] = $item;
        }

        return $shipmentItems;
    }
}
