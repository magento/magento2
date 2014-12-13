<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface ShipmentInterface
 */
interface ShipmentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const STORE_ID = 'store_id';
    const TOTAL_WEIGHT = 'total_weight';
    const TOTAL_QTY = 'total_qty';
    const EMAIL_SENT = 'email_sent';
    const ORDER_ID = 'order_id';
    const CUSTOMER_ID = 'customer_id';
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    const BILLING_ADDRESS_ID = 'billing_address_id';
    const SHIPMENT_STATUS = 'shipment_status';
    const INCREMENT_ID = 'increment_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const PACKAGES = 'packages';
    const SHIPPING_LABEL = 'shipping_label';
    const ITEMS = 'items';
    const TRACKS = 'tracks';
    const COMMENTS = 'comments';

    /**
     * Returns billing_address_id
     *
     * @return int
     */
    public function getBillingAddressId();

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Returns customer_id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Returns email_sent
     *
     * @return int
     */
    public function getEmailSent();

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Returns increment_id
     *
     * @return string
     */
    public function getIncrementId();

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Returns packages
     *
     * @return \Magento\Sales\Api\Data\ShipmentPackageInterface[]|null
     */
    public function getPackages();

    /**
     * Returns shipment_status
     *
     * @return int
     */
    public function getShipmentStatus();

    /**
     * Returns shipping_address_id
     *
     * @return int
     */
    public function getShippingAddressId();

    /**
     * Returns shipping_label
     *
     * @return string
     */
    public function getShippingLabel();

    /**
     * Returns store_id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Returns total_qty
     *
     * @return float
     */
    public function getTotalQty();

    /**
     * Returns total_weight
     *
     * @return float
     */
    public function getTotalWeight();

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Returns items
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface[]
     */
    public function getItems();

    /**
     * Returns tracks
     *
     * @return \Magento\Sales\Api\Data\ShipmentTrackInterface[]
     */
    public function getTracks();

    /**
     * Returns comments
     *
     * @return \Magento\Sales\Api\Data\ShipmentCommentInterface[]
     */
    public function getComments();
}
