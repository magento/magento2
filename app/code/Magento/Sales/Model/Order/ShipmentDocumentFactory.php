<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentPackageCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;

/**
 * Class ShipmentDocumentFactory
 *
 * @api
 */
class ShipmentDocumentFactory
{
    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * @var TrackFactory
     */
    private $trackFactory;

    /**
     * ShipmentDocumentFactory constructor.
     *
     * @param ShipmentFactory $shipmentFactory
     * @param TrackFactory $trackFactory
     */
    public function __construct(
        ShipmentFactory $shipmentFactory,
        TrackFactory $trackFactory
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param OrderInterface $order
     * @param ShipmentItemCreationInterface[] $items
     * @param ShipmentTrackCreationInterface[] $tracks
     * @param ShipmentCommentCreationInterface|null $comment
     * @param bool $appendComment
     * @param ShipmentPackageCreationInterface[] $packages
     * @param ShipmentCreationArgumentsInterface|null $arguments
     * @return ShipmentInterface
     */
    public function create(
        OrderInterface $order,
        array $items = [],
        array $tracks = [],
        ShipmentCommentCreationInterface $comment = null,
        $appendComment = false,
        array $packages = [],
        ShipmentCreationArgumentsInterface $arguments = null
    ) {
        $shipmentItems = $this->itemsToArray($items);
        /** @var Shipment $shipment */
        $shipment = $this->shipmentFactory->create(
            $order,
            $shipmentItems
        );
        $this->prepareTracks($shipment, $tracks);
        if ($comment) {
            $shipment->addComment(
                $comment->getComment(),
                $appendComment,
                $comment->getIsVisibleOnFront()
            );
        }

        return $shipment;
    }

    /**
     * Adds tracks to the shipment.
     *
     * @param ShipmentInterface $shipment
     * @param ShipmentTrackCreationInterface[] $tracks
     * @return ShipmentInterface
     */
    private function prepareTracks(\Magento\Sales\Api\Data\ShipmentInterface $shipment, array $tracks)
    {
        foreach ($tracks as $track) {
            $data = [];
            $data[ShipmentTrackInterface::CARRIER_CODE] = $track->getCarrierCode();
            $data[ShipmentTrackInterface::TITLE] = $track->getTitle();
            $data[ShipmentTrackInterface::TRACK_NUMBER] = $track->getTrackNumber();
            $shipment->addTrack($this->trackFactory->create(['data' => $data]));
        }
        return $shipment;
    }

    /**
     * Convert items to array
     *
     * @param ShipmentItemCreationInterface[] $items
     * @return array
     */
    private function itemsToArray(array $items = [])
    {
        $shipmentItems = [];
        foreach ($items as $item) {
            $shipmentItems[$item->getOrderItemId()] = $item->getQty();
        }
        return $shipmentItems;
    }
}
