<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Quote details interface.
 * @api
 * @since 2.0.0
 */
interface QuoteDetailsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get customer billing address
     *
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     * @since 2.0.0
     */
    public function getBillingAddress();

    /**
     * Set customer billing address
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $billingAddress
     * @return $this
     * @since 2.0.0
     */
    public function setBillingAddress(\Magento\Customer\Api\Data\AddressInterface $billingAddress = null);

    /**
     * Get customer shipping address
     *
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     * @since 2.0.0
     */
    public function getShippingAddress();

    /**
     * Set customer shipping address
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $shippingAddress
     * @return $this
     * @since 2.0.0
     */
    public function setShippingAddress(\Magento\Customer\Api\Data\AddressInterface $shippingAddress = null);

    /**
     * Get customer tax class key
     *
     * @return \Magento\Tax\Api\Data\TaxClassKeyInterface|null
     * @since 2.0.0
     */
    public function getCustomerTaxClassKey();

    /**
     * Set customer tax class key
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface $customerTaxClassKey
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerTaxClassKey(\Magento\Tax\Api\Data\TaxClassKeyInterface $customerTaxClassKey = null);

    /**
     * Get customer id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerId($customerId);

    /**
     * Get customer data
     *
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]|null
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set customer data
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null);

    /**
     * Get customer tax class id
     *
     * @return int
     * @since 2.0.0
     */
    public function getCustomerTaxClassId();

    /**
     * Set customer tax class id
     *
     * @param int $customerTaxClassId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerTaxClassId($customerTaxClassId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\QuoteDetailsExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\QuoteDetailsExtensionInterface $extensionAttributes);
}
