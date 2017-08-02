<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Shipment;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Item as ShipmentItemResource;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment as ShipmentCommentResource;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track as ShipmentTrackResource;

/**
 * Class Relation
 * @since 2.0.0
 */
class Relation implements RelationInterface
{
    /**
     * @var ShipmentItemResource
     * @since 2.0.0
     */
    protected $shipmentItemResource;

    /**
     * @var ShipmentTrackResource
     * @since 2.0.0
     */
    protected $shipmentTrackResource;

    /**
     * @var ShipmentCommentResource
     * @since 2.0.0
     */
    protected $shipmentCommentResource;

    /**
     * @param Item $shipmentItemResource
     * @param Track $shipmentTrackResource
     * @param Comment $shipmentCommentResource
     * @since 2.0.0
     */
    public function __construct(
        ShipmentItemResource $shipmentItemResource,
        ShipmentTrackResource $shipmentTrackResource,
        ShipmentCommentResource $shipmentCommentResource
    ) {
        $this->shipmentItemResource = $shipmentItemResource;
        $this->shipmentTrackResource = $shipmentTrackResource;
        $this->shipmentCommentResource = $shipmentCommentResource;
    }

    /**
     * Process relations for Shipment
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $object */
        if (null !== $object->getItems()) {
            foreach ($object->getItems() as $item) {
                $item->setParentId($object->getId());
                $this->shipmentItemResource->save($item);
            }
        }
        if (null !== $object->getTracks()) {
            foreach ($object->getTracks() as $track) {
                $track->setParentId($object->getId());
                $this->shipmentTrackResource->save($track);
            }
        }
        if (null !== $object->getComments()) {
            foreach ($object->getComments() as $comment) {
                $comment->setParentId($object->getId());
                $this->shipmentCommentResource->save($comment);
            }
        }
    }
}
