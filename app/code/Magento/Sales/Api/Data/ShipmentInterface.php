<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Shipment interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package.
 * @api
 * @since 2.0.0
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
     * @return int|null Billing address ID.
     * @since 2.0.0
     */
    public function getBillingAddressId();

    /**
     * Gets the created-at timestamp for the shipment.
     *
     * @return string|null Created-at timestamp.
     * @since 2.0.0
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the shipment.
     *
     * @param string $createdAt timestamp
     * @return $this
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the customer ID for the shipment.
     *
     * @return int|null Customer ID.
     * @since 2.0.0
     */
    public function getCustomerId();

    /**
     * Gets the email-sent flag value for the shipment.
     *
     * @return int|null Email-sent flag value.
     * @since 2.0.0
     */
    public function getEmailSent();

    /**
     * Gets the ID for the shipment.
     *
     * @return int|null Shipment ID.
     * @since 2.0.0
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     * @since 2.0.0
     */
    public function setEntityId($entityId);

    /**
     * Gets the increment ID for the shipment.
     *
     * @return string|null Increment ID.
     * @since 2.0.0
     */
    public function getIncrementId();

    /**
     * Gets the order ID for the shipment.
     *
     * @return int Order ID.
     * @since 2.0.0
     */
    public function getOrderId();

    /**
     * Gets any packages for the shipment.
     *
     * @return \Magento\Sales\Api\Data\ShipmentPackageInterface[]|null Array of packages, if any. Otherwise, null.
     * @since 2.0.0
     */
    public function getPackages();

    /**
     * Sets any packages for the shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentPackageInterface[] $packages
     * @return $this
     * @since 2.0.0
     */
    public function setPackages(array $packages = null);

    /**
     * Gets the shipment status.
     *
     * @return int|null Shipment status.
     * @since 2.0.0
     */
    public function getShipmentStatus();

    /**
     * Gets the shipping address ID for the shipment.
     *
     * @return int|null Shipping address ID.
     * @since 2.0.0
     */
    public function getShippingAddressId();

    /**
     * Gets the shipping label for the shipment.
     *
     * @return string|null Shipping label.
     * @since 2.0.0
     */
    public function getShippingLabel();

    /**
     * Gets the store ID for the shipment.
     *
     * @return int|null Store ID.
     * @since 2.0.0
     */
    public function getStoreId();

    /**
     * Gets the total quantity for the shipment.
     *
     * @return float|null Total quantity.
     * @since 2.0.0
     */
    public function getTotalQty();

    /**
     * Gets the total weight for the shipment.
     *
     * @return float|null Total weight.
     * @since 2.0.0
     */
    public function getTotalWeight();

    /**
     * Gets the updated-at timestamp for the shipment.
     *
     * @return string|null Updated-at timestamp.
     * @since 2.0.0
     */
    public function getUpdatedAt();

    /**
     * Gets the items for the shipment.
     *
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface[] Array of items.
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Sets the items for the shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems($items);

    /**
     * Gets the tracks for the shipment.
     *
     * @return \Magento\Sales\Api\Data\ShipmentTrackInterface[] Array of tracks.
     * @since 2.0.0
     */
    public function getTracks();

    /**
     * Sets the tracks for the shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentTrackInterface[] $tracks
     * @return $this
     * @since 2.0.0
     */
    public function setTracks($tracks);

    /**
     * Gets the comments for the shipment.
     *
     * @return \Magento\Sales\Api\Data\ShipmentCommentInterface[] Array of comments.
     * @since 2.0.0
     */
    public function getComments();

    /**
     * Sets the comments for the shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentCommentInterface[] $comments
     * @return $this
     * @since 2.0.0
     */
    public function setComments($comments = null);

    /**
     * Sets the store ID for the shipment.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($id);

    /**
     * Sets the total weight for the shipment.
     *
     * @param float $totalWeight
     * @return $this
     * @since 2.0.0
     */
    public function setTotalWeight($totalWeight);

    /**
     * Sets the total quantity for the shipment.
     *
     * @param float $qty
     * @return $this
     * @since 2.0.0
     */
    public function setTotalQty($qty);

    /**
     * Sets the email-sent flag value for the shipment.
     *
     * @param int $emailSent
     * @return $this
     * @since 2.0.0
     */
    public function setEmailSent($emailSent);

    /**
     * Sets the order ID for the shipment.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setOrderId($id);

    /**
     * Sets the customer ID for the shipment.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerId($id);

    /**
     * Sets the shipping address ID for the shipment.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setShippingAddressId($id);

    /**
     * Sets the billing address ID for the shipment.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setBillingAddressId($id);

    /**
     * Sets the shipment status.
     *
     * @param int $shipmentStatus
     * @return $this
     * @since 2.0.0
     */
    public function setShipmentStatus($shipmentStatus);

    /**
     * Sets the increment ID for the shipment.
     *
     * @param string $id
     * @return $this
     * @since 2.0.0
     */
    public function setIncrementId($id);

    /**
     * Sets the shipping label for the shipment.
     *
     * @param string $shippingLabel
     * @return $this
     * @since 2.0.0
     */
    public function setShippingLabel($shippingLabel);

    /**
     * Sets the updated-at timestamp for the shipment.
     *
     * @param string $timestamp
     * @return $this
     * @since 2.0.0
     */
    public function setUpdatedAt($timestamp);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\ShipmentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\ShipmentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\ShipmentExtensionInterface $extensionAttributes);
}
