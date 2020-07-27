<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\SalesGraphQl\Model\Shipment\Item\FormatterInterface;

/**
 * Get shipment item data
 */
class ItemProvider
{
    /**
     * @var FormatterInterface[]
     */
    private $formatters;

    /**
     * @param FormatterInterface[] $formatters
     */
    public function __construct(array $formatters = [])
    {
        $this->formatters = $formatters;
    }

    /**
     * Get item data for shipment
     *
     * @param ShipmentInterface $shipment
     * @return array
     */
    public function getItemData(ShipmentInterface $shipment): array
    {
        $shipmentItems = [];

        foreach ($shipment->getItems() as $shipmentItem) {
            $formattedItem = $this->formatItem($shipment, $shipmentItem);
            if ($formattedItem) {
                $shipmentItems[] = $formattedItem;
            }
        }
        return $shipmentItems;
    }

    /**
     * Format individual shipment item
     *
     * @param ShipmentInterface $shipment
     * @param ShipmentItemInterface $shipmentItem
     * @return array|null
     */
    private function formatItem(ShipmentInterface $shipment, ShipmentItemInterface $shipmentItem): ?array
    {
        $orderItem = $shipmentItem->getOrderItem();
        $formatter = $this->formatters[$orderItem->getProductType()] ?? $this->formatters['default'];

        return $formatter->formatShipmentItem($shipment, $shipmentItem);
    }
}
