<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api\Data;

/**
 * Interface ShippingAssignmentInterface
 * @api
 * @since 2.0.0
 */
interface ShippingAssignmentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return \Magento\Quote\Api\Data\ShippingInterface
     * @since 2.0.0
     */
    public function getShipping();

    /**
     * @param ShippingInterface $value
     * @return \Magento\Quote\Api\Data\ShippingInterface
     * @since 2.0.0
     */
    public function setShipping(\Magento\Quote\Api\Data\ShippingInterface $value);

    /**
     * @return \Magento\Quote\Api\Data\CartItemInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $value
     * @return mixed
     * @since 2.0.0
     */
    public function setItems($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\ShippingAssignmentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
    );
}
