<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Shipment comment interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package. A shipment document can contain comments.
 * @api
 * @since 2.0.0
 */
interface ShipmentCommentInterface extends ExtensibleDataInterface, CommentInterface, EntityInterface
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
     * Gets the is-customer-notified flag value for the shipment comment.
     *
     * @return int Is-customer-notified flag value.
     * @since 2.0.0
     */
    public function getIsCustomerNotified();

    /**
     * Gets the parent ID for the shipment comment.
     *
     * @return int Parent ID.
     * @since 2.0.0
     */
    public function getParentId();

    /**
     * Sets the parent ID for the shipment comment.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setParentId($id);

    /**
     * Sets the is-customer-notified flag value for the shipment comment.
     *
     * @param int $isCustomerNotified
     * @return $this
     * @since 2.0.0
     */
    public function setIsCustomerNotified($isCustomerNotified);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\ShipmentCommentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\ShipmentCommentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShipmentCommentExtensionInterface $extensionAttributes
    );
}
