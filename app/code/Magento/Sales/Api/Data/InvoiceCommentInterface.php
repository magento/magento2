<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Invoice comment interface.
 *
 * An invoice is a record of the receipt of payment for an order. An invoice can include comments that detail the
 * invoice history.
 * @api
 * @since 2.0.0
 */
interface InvoiceCommentInterface extends ExtensibleDataInterface, CommentInterface, EntityInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Parent ID.
     */
    const PARENT_ID = 'parent_id';
    /*
     * Is-customer-notified flag.
     */
    const IS_CUSTOMER_NOTIFIED = 'is_customer_notified';

    /**
     * Gets the is-customer-notified flag value for the invoice.
     *
     * @return int Is-customer-notified flag value.
     * @since 2.0.0
     */
    public function getIsCustomerNotified();

    /**
     * Gets the parent ID for the invoice.
     *
     * @return int Parent ID.
     * @since 2.0.0
     */
    public function getParentId();

    /**
     * Sets the parent ID for the invoice.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setParentId($id);

    /**
     * Sets the is-customer-notified flag value for the invoice.
     *
     * @param int $isCustomerNotified
     * @return $this
     * @since 2.0.0
     */
    public function setIsCustomerNotified($isCustomerNotified);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\InvoiceCommentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\InvoiceCommentExtensionInterface $extensionAttributes
    );
}
