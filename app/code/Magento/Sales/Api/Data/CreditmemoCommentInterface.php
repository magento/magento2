<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Credit memo comment interface.
 *
 * After a customer places and pays for an order and an invoice has been issued, the merchant can create a credit memo
 * to refund all or part of the amount paid for any returned or undelivered items. The memo restores funds to the
 * customer account so that the customer can make future purchases. A credit memo usually includes comments that detail
 * why the credit memo amount was credited to the customer.
 * @api
 * @since 2.0.0
 */
interface CreditmemoCommentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     * Gets the credit memo comment.
     *
     * @return string Comment.
     * @since 2.0.0
     */
    public function getComment();

    /**
     * Gets the credit memo created-at timestamp.
     *
     * @return string|null Created-at timestamp.
     * @since 2.0.0
     */
    public function getCreatedAt();

    /**
     * Sets the credit memo created-at timestamp.
     *
     * @param string $createdAt timestamp
     * @return $this
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the credit memo ID.
     *
     * @return int|null Credit memo ID.
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
     * Gets the is-customer-notified flag value for the credit memo.
     *
     * @return int Is-customer-notified flag value.
     * @since 2.0.0
     */
    public function getIsCustomerNotified();

    /**
     * Gets the is-visible-on-storefront flag value for the credit memo.
     *
     * @return int Is-visible-on-storefront flag value.
     * @since 2.0.0
     */
    public function getIsVisibleOnFront();

    /**
     * Gets the parent ID for the credit memo.
     *
     * @return int Parent ID.
     * @since 2.0.0
     */
    public function getParentId();

    /**
     * Sets the parent ID for the credit memo.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setParentId($id);

    /**
     * Sets the is-customer-notified flag value for the credit memo.
     *
     * @param int $isCustomerNotified
     * @return $this
     * @since 2.0.0
     */
    public function setIsCustomerNotified($isCustomerNotified);

    /**
     * Sets the is-visible-on-storefront flag value for the credit memo.
     *
     * @param int $isVisibleOnFront
     * @return $this
     * @since 2.0.0
     */
    public function setIsVisibleOnFront($isVisibleOnFront);

    /**
     * Sets the credit memo comment.
     *
     * @param string $comment
     * @return $this
     * @since 2.0.0
     */
    public function setComment($comment);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoCommentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoCommentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoCommentExtensionInterface $extensionAttributes
    );
}
