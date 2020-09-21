<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Order\Shipment;

use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\SalesGraphQl\Model\Shipment\Item\ShipmentItemFormatter;
use Magento\SalesGraphQl\Model\Shipment\Item\FormatterInterface;

/**
 * Format Bundle shipment items for GraphQl output
 */
class BundleShipmentItemFormatter implements FormatterInterface
{
    /**
     * @var ShipmentItemFormatter
     */
    private $itemFormatter;

    /**
     * @param ShipmentItemFormatter $itemFormatter
     */
    public function __construct(ShipmentItemFormatter $itemFormatter)
    {
        $this->itemFormatter = $itemFormatter;
    }

    /**
     * Format bundle product shipment item
     *
     * @param ShipmentInterface $shipment
     * @param ShipmentItemInterface $item
     * @return array|null
     */
    public function formatShipmentItem(ShipmentInterface $shipment, ShipmentItemInterface $item): ?array
    {
        $orderItem = $item->getOrderItem();
        $shippingType = $orderItem->getProductOptions()['shipment_type'] ?? null;
        if ($shippingType == AbstractType::SHIPMENT_SEPARATELY && !$orderItem->getParentItemId()) {
            //When bundle items are shipped separately the children are treated as their own items
            return null;
        }
        return $this->itemFormatter->formatShipmentItem($shipment, $item);
    }
}
