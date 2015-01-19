<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Shipment interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package.
 */
interface ShipmentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Store ID.
     */
    const STORE_ID = 'store_id';
    /*
     * Total weight.
     */
    const TOTAL_WEIGHT = 'total_weight';
    /*
     * Total quantity.
     */
    const TOTAL_QTY = 'total_qty';
    /*
     * Email sent flag.
     */
    const EMAIL_SENT = 'email_sent';
    /*
     * Order ID.
     */
    const ORDER_ID = 'order_id';
    /*
     * Customer ID.
     */
    const CUSTOMER_ID = 'customer_id';
    /*
     * Shipping address ID.
     */
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    /*
     * Billing address ID.
     */
    const BILLING_ADDRESS_ID = 'billing_address_id';
    /*
     * Shipment status.
     */
    const SHIPMENT_STATUS = 'shipment_status';
    /*
     * Increment ID.
     */
    const INCREMENT_ID = 'increment_id';
    /*
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Updated-at timestamp.
     */
    const UPDATED_AT = 'updated_at';
    /*
     * Packages.
     */
    const PACKAGES = 'packages';
    /*
     * Shipping label.
     */
    const SHIPPING_LABEL = 'shipping_label';
    /*
     * Items.
     */
    const ITEMS = 'items';
    /*
     * Tracks.
     */
    const TRACKS = 'tracks';
    /*
     * Comments.
     */
    const COMMENTS = 'comments';

    /**
     * Gets the billing address ID for the shipment.
     *
     * @return int Billing address ID.
     */
    public function getBillingAddressId();

    /**
     * Gets the created-at timestamp for the shipment.
     *
     * @return string Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Gets the customer ID for the shipment.
     *
     * @return int Customer ID.
     */
    public function getCustomerId();

    /**
     * Gets the email-sent flag value for the shipment.
     *
     * @return int Email-sent flag value.
     */
    public function getEmailSent();

    /**
     * Gets the ID for the shipment.
     *
     * @return int Shipment ID.
     */
    public function getEntityId();

    /**
     * Gets the increment ID for the shipment.
     *
     * @return string Increment ID.
     */
    public function getIncrementId();

    /**
     * Gets the order ID for the shipment.
     *
     * @return int Order ID.
     */
    public function getOrderId();

    /**
     * Gets any packages for the shipment.
     *
     * @return \Magento\Sales\Api\Data\ShipmentPackageInterface[]|null Array of packages, if any. Otherwise, null.
     */
    public function getPackages();

    /**
     * Gets the shipment status.
     *
     * @return int Shipment status.
     */
    public function getShipmentStatus();

    /**
     * Gets the shipping address ID for the shipment.
     *
     * @return int Shipping address ID.
     */
    public function getShippingAddressId();

    /**
     * Gets the shipping label for the shipment.
     *
     * @return string Shipping label.
     */
    public function getShippingLabel();

    /**
     * Gets the store ID for the shipment.
     *
     * @return int Store ID.
     */
    public function getStoreId();

    /**
     * Gets the total quantity for the shipment.
     *
     * @return float Total quantity.
     */
    public function getTotalQty();

    /**
     * Gets the total weight for the shipment.
     *
     * @return float Total weight.
     */
    public function getTotalWeight();

    /**
     * Gets the updated-at timestamp for the shipment.
     *
     * @return string Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets the items for the shipment.
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface[] Array of items.
     */
    public function getItems();

    /**
     * Gets the tracks for the shipment.
     *
     * @return \Magento\Sales\Api\Data\ShipmentTrackInterface[] Array of tracks.
     */
    public function getTracks();

    /**
     * Gets the comments for the shipment.
     *
     * @return \Magento\Sales\Api\Data\ShipmentCommentInterface[] Array of comments.
     */
    public function getComments();
}
