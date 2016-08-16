<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentPackageInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;

/**
 * Class InvoiceDocumentFactory
 *
 * @api
 */
class ShipmentDocumentFactory
{
    private $shipmentFactory;

    public function __construct(
        ShipmentFactory $shipmentFactory
    ) {
        $this->shipmentFactory = $shipmentFactory;
    }

    /**
     * @param OrderInterface $order
     * @param array $items
     * @param array $tracks
     * @param ShipmentCommentCreationInterface|null $comment
     * @param bool $appendComment
     * @param array $packages
     * @return Shipment
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
            $shipmentItems,
            $tracks
        );

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
