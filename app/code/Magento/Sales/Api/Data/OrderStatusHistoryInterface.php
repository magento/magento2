<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order status history interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 * @since 2.0.0
 */
interface OrderStatusHistoryInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     *  Is-customer-notified flag.
     */
    const IS_CUSTOMER_NOTIFIED = 'is_customer_notified';
    /*
     * Is-visible-on-storefront flag.
     */
    const IS_VISIBLE_ON_FRONT = 'is_visible_on_front';
    /*
     * Comment.
     */
    const COMMENT = 'comment';
    /*
     * Status.
     */
    const STATUS = 'status';
    /*
     * Create-at timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Entity name.
     */
    const ENTITY_NAME = 'entity_name';

    /**
     * Gets the comment for the order status history.
     *
     * @return string Comment.
     * @since 2.0.0
     */
    public function getComment();

    /**
     * Gets the created-at timestamp for the order status history.
     *
     * @return string|null Created-at timestamp.
     * @since 2.0.0
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the order status history.
     *
     * @param string $createdAt timestamp
     * @return $this
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the ID for the order status history.
     *
     * @return int|null Order status history ID.
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
     * Gets the entity name for the order status history.
     *
     * @return string|null Entity name.
     * @since 2.0.0
     */
    public function getEntityName();

    /**
     * Gets the is-customer-notified flag value for the order status history.
     *
     * @return int Is-customer-notified flag value.
     * @since 2.0.0
     */
    public function getIsCustomerNotified();

    /**
     * Gets the is-visible-on-storefront flag value for the order status history.
     *
     * @return int Is-visible-on-storefront flag value.
     * @since 2.0.0
     */
    public function getIsVisibleOnFront();

    /**
     * Gets the parent ID for the order status history.
     *
     * @return int Parent ID.
     * @since 2.0.0
     */
    public function getParentId();

    /**
     * Gets the status for the order status history.
     *
     * @return string|null Status.
     * @since 2.0.0
     */
    public function getStatus();

    /**
     * Sets the parent ID for the order status history.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setParentId($id);

    /**
     * Sets the is-customer-notified flag value for the order status history.
     *
     * @param int $isCustomerNotified
     * @return $this
     * @since 2.0.0
     */
    public function setIsCustomerNotified($isCustomerNotified);

    /**
     * Sets the is-visible-on-storefront flag value for the order status history.
     *
     * @param int $isVisibleOnFront
     * @return $this
     * @since 2.0.0
     */
    public function setIsVisibleOnFront($isVisibleOnFront);

    /**
     * Sets the comment for the order status history.
     *
     * @param string $comment
     * @return $this
     * @since 2.0.0
     */
    public function setComment($comment);

    /**
     * Sets the status for the order status history.
     *
     * @param string $status
     * @return $this
     * @since 2.0.0
     */
    public function setStatus($status);

    /**
     * Sets the entity name for the order status history.
     *
     * @param string $entityName
     * @return $this
     * @since 2.0.0
     */
    public function setEntityName($entityName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\OrderStatusHistoryExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\OrderStatusHistoryExtensionInterface $extensionAttributes
    );
}
