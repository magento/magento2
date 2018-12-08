<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
declare(strict_types=1);

namespace Magento\Sales\Plugin;

<<<<<<< HEAD
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentSearchResultInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;

/**
 * Plugin to convert shipping label from blob to base64encoded string.
=======
/**
 * Plugin to convert shipping label from blob to base64encoded string
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class ShippingLabelConverter
{
    /**
<<<<<<< HEAD
     * Convert shipping label from blob to base64encoded string.
     *
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentSearchResultInterface $searchResult
     * @return ShipmentSearchResultInterface
=======
     * Convert shipping label from blob to base64encoded string
     *
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Api\Data\ShipmentSearchResultInterface $searchResult
     * @return \Magento\Sales\Api\Data\ShipmentSearchResultInterface
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
<<<<<<< HEAD
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentSearchResultInterface $searchResult
    ): ShipmentSearchResultInterface {
        /** @var Shipment $item */
=======
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\Data\ShipmentSearchResultInterface $searchResult
    ) {
        /** @var \Magento\Sales\Model\Order\Shipment $item */
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        foreach ($searchResult->getItems() as $item) {
            if ($item->getShippingLabel() !== null) {
                $item->setShippingLabel(base64_encode($item->getShippingLabel()));
            }
        }
<<<<<<< HEAD

=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        return $searchResult;
    }

    /**
<<<<<<< HEAD
     * Convert shipping label from blob to base64encoded string.
     *
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentInterface $shipment
     * @return ShipmentInterface
=======
     * Convert shipping label from blob to base64encoded string
     *
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @return \Magento\Sales\Api\Data\ShipmentInterface
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
<<<<<<< HEAD
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentInterface $shipment
    ): ShipmentInterface {
        if ($shipment->getShippingLabel() !== null) {
            $shipment->setShippingLabel(base64_encode($shipment->getShippingLabel()));
        }

=======
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\Data\ShipmentInterface $shipment
    ) {
        if ($shipment->getShippingLabel() !== null) {
            $shipment->setShippingLabel(base64_encode($shipment->getShippingLabel()));
        }
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        return $shipment;
    }
}
