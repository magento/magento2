<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api\Data;

/**
 * Interface ShippingInterface
 * @api
 * @since 2.0.0
 */
interface ShippingInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get shipping Address
     *
     * @return \Magento\Quote\Api\Data\AddressInterface
     * @since 2.0.0
     */
    public function getAddress();

    /**
     * Set shipping address
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $value
     * @return void
     * @since 2.0.0
     */
    public function setAddress(\Magento\Quote\Api\Data\AddressInterface $value);

    /**
     * Get shipping method
     *
     * @return string
     * @since 2.0.0
     */
    public function getMethod();

    /**
     * Set shipping method
     *
     * @param string $value
     * @return void
     * @since 2.0.0
     */
    public function setMethod($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\ShippingExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\ShippingExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\ShippingExtensionInterface $extensionAttributes);
}
