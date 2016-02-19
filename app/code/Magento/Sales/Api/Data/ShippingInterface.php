<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface ShippingInterface
 */
interface ShippingInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Shipping object data keys
     */
    const KEY_ADDRESS = 'address';

    const KEY_METHOD = 'method';

    const KEY_TOTAL = 'total';
    /**#@-*/

    /**
     * Gets shipping address
     *
     * @return \Magento\Sales\Api\Data\OrderAddressInterface
     */
    public function getAddress();

    /**
     * Gets shipping method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Gets object with shipping totals
     *
     * @return \Magento\Sales\Api\Data\TotalInterface
     */
    public function getTotal();

    /**
     * Sets address to shipping
     *
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $address
     * @return $this
     */
    public function setAddress(\Magento\Sales\Api\Data\OrderAddressInterface $address);

    /**
     * Sets method to shipping
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method);

    /**
     * Sets total object to shipping
     *
     * @param \Magento\Sales\Api\Data\TotalInterface $total
     * @return $this
     */
    public function setTotal(\Magento\Sales\Api\Data\TotalInterface $total);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\ShippingExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\ShippingExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\ShippingExtensionInterface $extensionAttributes
    );
}
