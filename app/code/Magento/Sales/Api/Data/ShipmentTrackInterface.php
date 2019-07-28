<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Shipment track interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package. Merchants and customers can track
 * shipments.
 * @api
 * @since 100.0.2
 */
interface ShipmentTrackInterface extends TrackInterface, ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Parent ID.
     */
    const PARENT_ID = 'parent_id';
    /*
     * Weight.
     */
    const WEIGHT = 'weight';
    /*
     * Quantity.
     */
    const QTY = 'qty';
    /*
     * Order ID.
     */
    const ORDER_ID = 'order_id';
    /*
     * Track number.
     */
    const TRACK_NUMBER = 'track_number';
    /*
     * Description.
     */
    const DESCRIPTION = 'description';
    /*
     * Title.
     */
    const TITLE = 'title';
    /*
     * Carrier code.
     */
    const CARRIER_CODE = 'carrier_code';
    /*
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Updated-at timestamp.
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Sets the order_id for the shipment package.
     *
     * @param int $id
     * @return $this
     */
    public function setOrderId($id);

    /**
     * Gets the order_id for the shipment package.
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Gets the created-at timestamp for the shipment package.
     *
     * @return string|null Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the shipment package.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the ID for the shipment package.
     *
     * @return int|null Shipment package ID.
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Gets the parent ID for the shipment package.
     *
     * @return int Parent ID.
     */
    public function getParentId();

    /**
     * Gets the updated-at timestamp for the shipment package.
     *
     * @return string|null Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Sets the updated-at timestamp for the shipment package.
     *
     * @param string $timestamp
     * @return $this
     */
    public function setUpdatedAt($timestamp);

    /**
     * Sets the parent ID for the shipment package.
     *
     * @param int $id
     * @return $this
     */
    public function setParentId($id);

    /**
     * Sets the weight for the shipment package.
     *
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);

    /**
     * Gets the weight for the shipment package.
     *
     * @return float Weight.
     */
    public function getWeight();

    /**
     * Sets the quantity for the shipment package.
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Gets the quantity for the shipment package.
     *
     * @return float Quantity.
     */
    public function getQty();

    /**
     * Sets the description for the shipment package.
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Gets the description for the shipment package.
     *
     * @return string Description.
     */
    public function getDescription();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\ShipmentTrackExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\ShipmentTrackExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentTrackExtensionInterface $extensionAttributes
    );
}
