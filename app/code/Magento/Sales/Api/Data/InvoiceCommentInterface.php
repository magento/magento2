<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Invoice comment interface.
 *
 * An invoice is a record of the receipt of payment for an order. An invoice can include comments that detail the
 * invoice history.
 * @api
 */
interface InvoiceCommentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     * Is-customer-notified flag.
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
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';

    /**
     * Gets the comment for the invoice.
     *
     * @return string Comment.
     */
    public function getComment();

    /**
     * Gets the created-at timestamp for the invoice.
     *
     * @return string|null Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the invoice.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the ID for the invoice.
     *
     * @return int|null Invoice ID.
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
     * Gets the is-customer-notified flag value for the invoice.
     *
     * @return int Is-customer-notified flag value.
     */
    public function getIsCustomerNotified();

    /**
     * Gets the is-visible-on-storefront flag value for the invoice.
     *
     * @return int Is-visible-on-storefront flag value.
     */
    public function getIsVisibleOnFront();

    /**
     * Gets the parent ID for the invoice.
     *
     * @return int Parent ID.
     */
    public function getParentId();

    /**
     * Sets the parent ID for the invoice.
     *
     * @param int $id
     * @return $this
     */
    public function setParentId($id);

    /**
     * Sets the is-customer-notified flag value for the invoice.
     *
     * @param int $isCustomerNotified
     * @return $this
     */
    public function setIsCustomerNotified($isCustomerNotified);

    /**
     * Sets the is-visible-on-storefront flag value for the invoice.
     *
     * @param int $isVisibleOnFront
     * @return $this
     */
    public function setIsVisibleOnFront($isVisibleOnFront);

    /**
     * Sets the comment for the invoice.
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\InvoiceCommentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\InvoiceCommentExtensionInterface $extensionAttributes
    );
}
