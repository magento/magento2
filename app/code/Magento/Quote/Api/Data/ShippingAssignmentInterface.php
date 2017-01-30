<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api\Data;

/**
 * Interface ShippingAssignmentInterface
 */
interface ShippingAssignmentInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return \Magento\Quote\Api\Data\ShippingInterface
     */
    public function getShipping();

    /**
     * @param ShippingInterface $value
     * @return \Magento\Quote\Api\Data\ShippingInterface
     */
    public function setShipping(\Magento\Quote\Api\Data\ShippingInterface $value);

    /**
     * @return \Magento\Quote\Api\Data\CartItemInterface[]
     */
    public function getItems();

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $value
     * @return mixed
     */
    public function setItems($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\ShippingAssignmentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\ShippingAssignmentExtensionInterface $extensionAttributes
    );
}
