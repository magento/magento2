<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Shipment track interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package. Merchants and customers can track
 * shipments.
 */
interface ShipmentTrackInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     * Gets the carrier code for the shipment package.
     *
     * @return string Carrier code.
     */
    public function getCarrierCode();

    /**
     * Gets the created-at timestamp for the shipment package.
     *
     * @return string Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Gets the description for the shipment package.
     *
     * @return string Description.
     */
    public function getDescription();

    /**
     * Gets the ID for the shipment package.
     *
     * @return int Shipment package ID.
     */
    public function getEntityId();

    /**
     * Gets the order_id for the shipment package.
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Gets the parent ID for the shipment package.
     *
     * @return int Parent ID.
     */
    public function getParentId();

    /**
     * Gets the quantity for the shipment package.
     *
     * @return float Quantity.
     */
    public function getQty();

    /**
     * Gets the title for the shipment package.
     *
     * @return string Title.
     */
    public function getTitle();

    /**
     * Gets the track number for the shipment package.
     *
     * @return string Track number.
     */
    public function getTrackNumber();

    /**
     * Gets the updated-at timestamp for the shipment package.
     *
     * @return string Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets the weight for the shipment package.
     *
     * @return float Weight.
     */
    public function getWeight();
}
