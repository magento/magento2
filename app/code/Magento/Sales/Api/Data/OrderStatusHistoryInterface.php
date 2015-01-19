<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order status history interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
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
     */
    public function getComment();

    /**
     * Gets the created-at timestamp for the order status history.
     *
     * @return string Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Gets the ID for the order status history.
     *
     * @return int Order status history ID.
     */
    public function getEntityId();

    /**
     * Gets the entity name for the order status history.
     *
     * @return string Entity name.
     */
    public function getEntityName();

    /**
     * Gets the is-customer-notified flag value for the order status history.
     *
     * @return int Is-customer-notified flag value.
     */
    public function getIsCustomerNotified();

    /**
     * Gets the is-visible-on-storefront flag value for the order status history.
     *
     * @return int Is-visible-on-storefront flag value.
     */
    public function getIsVisibleOnFront();

    /**
     * Gets the parent ID for the order status history.
     *
     * @return int Parent ID.
     */
    public function getParentId();

    /**
     * Gets the status for the order status history.
     *
     * @return string Status.
     */
    public function getStatus();
}
