<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Framework\EntityManager\HydratorPool;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentPackageInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory;

/**
 * Class InvoiceDocumentFactory
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
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * ShipmentDocumentFactory constructor.
     *
     * @param ShipmentFactory $shipmentFactory
     * @param HydratorPool $hydratorPool
     * @param TrackFactory $trackFactory
     */
    public function __construct(
        ShipmentFactory $shipmentFactory,
        HydratorPool $hydratorPool,
        TrackFactory $trackFactory
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
        $this->hydratorPool = $hydratorPool;
    }

    /**
     * @param OrderInterface $order
     * @param array $items
     * @param array $tracks
     * @param ShipmentCommentCreationInterface|null $comment
     * @param bool $appendComment
     * @param array $packages
     * @return Shipment
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(
        OrderInterface $order,
        $items = [],
        $tracks = [],
        ShipmentCommentCreationInterface $comment = null,
        $appendComment = false,
        $packages = []
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
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param \Magento\Sales\Api\Data\ShipmentTrackCreationInterface[] $tracks
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    private function prepareTracks(\Magento\Sales\Api\Data\ShipmentInterface $shipment, array $tracks)
    {
        foreach ($tracks as $track) {
            if (!$track->getTrackNumber()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a tracking number.')
                );
            }
            $hydrator = $this->hydratorPool->getHydrator(ShipmentTrackCreationInterface::class);
            $shipment->addTrack($this->trackFactory->create($hydrator->extract($track)));
        }

        return $shipment;
    }


    /**
     * Convert Items To Array
     *
     * @param InvoiceItemCreationInterface[] $items
     * @return array
     */
    private function itemsToArray($items = [])
    {
        $invoiceItems = [];
        foreach ($items as $item) {
            $invoiceItems[$item->getOrderItemId()] = $item->getQty();
        }
        return $invoiceItems;
    }
}
