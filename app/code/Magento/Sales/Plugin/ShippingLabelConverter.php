<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Plugin;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentSearchResultInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;

/**
 * Plugin to convert shipping label from blob to base64encoded string.
 */
class ShippingLabelConverter
{
    /**
     * Convert shipping label from blob to base64encoded string.
     *
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentSearchResultInterface $searchResult
     * @return ShipmentSearchResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentSearchResultInterface $searchResult
    ): ShipmentSearchResultInterface {
        /** @var Shipment $item */
        foreach ($searchResult->getItems() as $item) {
            if ($item->getShippingLabel() !== null) {
                $item->setShippingLabel(base64_encode($item->getShippingLabel()));
            }
        }

        return $searchResult;
    }

    /**
     * Convert shipping label from blob to base64encoded string.
     *
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentInterface $shipment
     * @return ShipmentInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentInterface $shipment
    ): ShipmentInterface {
        if ($shipment->getShippingLabel() !== null) {
            $shipment->setShippingLabel(base64_encode($shipment->getShippingLabel()));
        }

        return $shipment;
    }
}
