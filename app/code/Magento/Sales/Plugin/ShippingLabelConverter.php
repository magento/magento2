<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin;

/**
 * Plugin to convert shipping label from blob to base64encoded string
 */
class ShippingLabelConverter
{
    /**
     * Convert shipping label from blob to base64encoded string
     *
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Api\Data\ShipmentSearchResultInterface $searchResult
     * @return \Magento\Sales\Api\Data\ShipmentSearchResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\Data\ShipmentSearchResultInterface $searchResult
    ) {
        /** @var \Magento\Sales\Model\Order\Shipment $item */
        foreach ($searchResult->getItems() as $item) {
            if ($item->getShippingLabel() !== null) {
                $item->setShippingLabel(base64_encode($item->getShippingLabel()));
            }
        }
        return $searchResult;
    }

    /**
     * Convert shipping label from blob to base64encoded string
     *
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\Data\ShipmentInterface $shipment
    ) {
        if ($shipment->getShippingLabel() !== null) {
            $shipment->setShippingLabel(base64_encode($shipment->getShippingLabel()));
        }
        return $shipment;
    }
}
