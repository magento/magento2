<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api\Data;

/**
 * Interface ShippingInterface
 * @api
 */
interface ShippingInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get shipping Address
     *
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getAddress();

    /**
     * Set shipping address
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $value
     * @return void
     */
    public function setAddress(\Magento\Quote\Api\Data\AddressInterface $value);


    /**
     * Get shipping method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set shipping method
     *
     * @param string $value
     * @return void
     */
    public function setMethod($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\ShippingExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\ShippingExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\ShippingExtensionInterface $extensionAttributes);
}
