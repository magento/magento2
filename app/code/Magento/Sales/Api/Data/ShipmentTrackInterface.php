<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface ShipmentTrackInterface
 */
interface ShipmentTrackInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const WEIGHT = 'weight';
    const QTY = 'qty';
    const ORDER_ID = 'order_id';
    const TRACK_NUMBER = 'track_number';
    const DESCRIPTION = 'description';
    const TITLE = 'title';
    const CARRIER_CODE = 'carrier_code';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Returns carrier_code
     *
     * @return string
     */
    public function getCarrierCode();

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId();

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty();

    /**
     * Returns title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns track_number
     *
     * @return string
     */
    public function getTrackNumber();

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight();
}
