<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

/**
 * Class InvoiceDocumentFactory
 *
 * @api
 */
class ShipmentDocumentFactory
{
    /**
     * @var \Magento\Framework\EntityManager\HydratorPool
     */
    private $shipmentFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    private $trackFactory;

    /**
     * @var \Magento\Framework\EntityManager\HydratorPool
     */
    private $hydratorPool;

    /**
     * ShipmentDocumentFactory constructor.
     *
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Framework\EntityManager\HydratorPool $hydratorPool
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     */
    public function __construct(
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\EntityManager\HydratorPool $hydratorPool,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
        $this->hydratorPool = $hydratorPool;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\ShipmentItemCreationInterface[] $items
     * @param \Magento\Sales\Api\Data\ShipmentTrackCreationInterface[] $tracks
     * @param \Magento\Sales\Api\Data\ShipmentCommentCreationInterface|null $comment
     * @param bool $appendComment
     * @param \Magento\Sales\Api\Data\ShipmentPackageInterface[] $packages
     * @param \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface|null $arguments
     * @return Shipment
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(
        \Magento\Sales\Api\Data\OrderInterface $order,
        array $items = [],
        array $tracks = [],
        \Magento\Sales\Api\Data\ShipmentCommentCreationInterface $comment = null,
        $appendComment = false,
        array $packages = [],
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface $arguments = null
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
            $hydrator = $this->hydratorPool->getHydrator(
                \Magento\Sales\Api\Data\ShipmentTrackCreationInterface::class
            );
            $shipment->addTrack($this->trackFactory->create(['data' => $hydrator->extract($track)]));
        }
        return $shipment;
    }

    /**
     * Convert Items To Array
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemCreationInterface[] $items
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
