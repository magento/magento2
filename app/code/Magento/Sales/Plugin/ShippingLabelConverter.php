<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
declare(strict_types=1);

namespace Magento\Sales\Plugin;

<<<<<<< HEAD
/**
 * Plugin to convert shipping label from blob to base64encoded string
=======
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentSearchResultInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;

/**
 * Plugin to convert shipping label from blob to base64encoded string.
>>>>>>> upstream/2.2-develop
 */
class ShippingLabelConverter
{
    /**
<<<<<<< HEAD
     * Convert shipping label from blob to base64encoded string
     *
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Api\Data\ShipmentSearchResultInterface $searchResult
     * @return \Magento\Sales\Api\Data\ShipmentSearchResultInterface
=======
     * Convert shipping label from blob to base64encoded string.
     *
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentSearchResultInterface $searchResult
     * @return ShipmentSearchResultInterface
>>>>>>> upstream/2.2-develop
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
<<<<<<< HEAD
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\Data\ShipmentSearchResultInterface $searchResult
    ) {
        /** @var \Magento\Sales\Model\Order\Shipment $item */
=======
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentSearchResultInterface $searchResult
    ): ShipmentSearchResultInterface {
        /** @var Shipment $item */
>>>>>>> upstream/2.2-develop
        foreach ($searchResult->getItems() as $item) {
            if ($item->getShippingLabel() !== null) {
                $item->setShippingLabel(base64_encode($item->getShippingLabel()));
            }
        }
<<<<<<< HEAD
=======

>>>>>>> upstream/2.2-develop
        return $searchResult;
    }

    /**
<<<<<<< HEAD
     * Convert shipping label from blob to base64encoded string
     *
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @return \Magento\Sales\Api\Data\ShipmentInterface
=======
     * Convert shipping label from blob to base64encoded string.
     *
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentInterface $shipment
     * @return ShipmentInterface
>>>>>>> upstream/2.2-develop
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
<<<<<<< HEAD
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\Data\ShipmentInterface $shipment
    ) {
        if ($shipment->getShippingLabel() !== null) {
            $shipment->setShippingLabel(base64_encode($shipment->getShippingLabel()));
        }
=======
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentInterface $shipment
    ): ShipmentInterface {
        if ($shipment->getShippingLabel() !== null) {
            $shipment->setShippingLabel(base64_encode($shipment->getShippingLabel()));
        }

>>>>>>> upstream/2.2-develop
        return $shipment;
    }
}
