<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface ShippingInterface
 * @api
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
     * @return \Magento\Sales\Api\Data\OrderAddressInterface|null
     */
    public function getAddress();

    /**
     * Gets shipping method
     *
     * @return string|null
     */
    public function getMethod();

    /**
     * Gets object with shipping totals
     *
     * @return \Magento\Sales\Api\Data\TotalInterface|null
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
