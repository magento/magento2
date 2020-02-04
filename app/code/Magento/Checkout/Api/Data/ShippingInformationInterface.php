<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api\Data;

/**
 * Interface ShippingInformationInterface
 * @api
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
     */
    public function getShippingAddress();

    /**
     * Set shipping address
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function setShippingAddress(\Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * Returns billing address
     *
     * @return \Magento\Quote\Api\Data\AddressInterface|null
     */
    public function getBillingAddress();

    /**
     * Set billing address if additional synchronization needed
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function setBillingAddress(\Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * Returns shipping method code
     *
     * @return string
     */
    public function getShippingMethodCode();

    /**
     * Set shipping method code
     *
     * @param string $code
     * @return $this
     */
    public function setShippingMethodCode($code);

    /**
     * Returns carrier code
     *
     * @return string
     */
    public function getShippingCarrierCode();

    /**
     * Set carrier code
     *
     * @param string $code
     * @return $this
     */
    public function setShippingCarrierCode($code);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Checkout\Api\Data\ShippingInformationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Checkout\Api\Data\ShippingInformationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Checkout\Api\Data\ShippingInformationExtensionInterface $extensionAttributes
    );
}
