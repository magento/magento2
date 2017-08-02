<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api\Data;

/**
 * Interface ShippingInformationInterface
 * @api
 * @since 2.0.0
 */
interface ShippingInformationInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const SHIPPING_ADDRESS = 'shipping_address';

    const BILLING_ADDRESS = 'billing_address';

    const SHIPPING_METHOD_CODE = 'shipping_method_code';

    const SHIPPING_CARRIER_CODE = 'shipping_carrier_code';

    /**#@-*/

    /**
     * Returns shipping address
     *
     * @return \Magento\Quote\Api\Data\AddressInterface
     * @since 2.0.0
     */
    public function getShippingAddress();

    /**
     * Set shipping address
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     * @since 2.0.0
     */
    public function setShippingAddress(\Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * Returns billing address
     *
     * @return \Magento\Quote\Api\Data\AddressInterface|null
     * @since 2.0.0
     */
    public function getBillingAddress();

    /**
     * Set billing address if additional synchronization needed
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     * @since 2.0.0
     */
    public function setBillingAddress(\Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * Returns shipping method code
     *
     * @return string
     * @since 2.0.0
     */
    public function getShippingMethodCode();

    /**
     * Set shipping method code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setShippingMethodCode($code);

    /**
     * Returns carrier code
     *
     * @return string
     * @since 2.0.0
     */
    public function getShippingCarrierCode();

    /**
     * Set carrier code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setShippingCarrierCode($code);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Checkout\Api\Data\ShippingInformationExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Checkout\Api\Data\ShippingInformationExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Checkout\Api\Data\ShippingInformationExtensionInterface $extensionAttributes
    );
}
