<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;


interface QuoteDetailsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_BILLING_ADDRESS = 'billing_address';

    const KEY_SHIPPING_ADDRESS = 'shipping_address';

    const KEY_CUSTOMER_TAX_CLASS_KEY = 'customer_tax_class_key';

    const KEY_ITEMS = 'items';

    const CUSTOMER_TAX_CLASS_ID = 'customer_tax_class_id';

    const KEY_CUSTOMER_ID = 'customer_id';
    /**#@-*/

    /**
     * Get customer billing address
     *
     * @api
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getBillingAddress();

    /**
     * Set customer billing address
     *
     * @api
     * @param \Magento\Customer\Api\Data\AddressInterface $billingAddress
     * @return $this
     */
    public function setBillingAddress(\Magento\Customer\Api\Data\AddressInterface $billingAddress = null);

    /**
     * Get customer shipping address
     *
     * @api
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getShippingAddress();

    /**
     * Set customer shipping address
     *
     * @api
     * @param \Magento\Customer\Api\Data\AddressInterface $shippingAddress
     * @return $this
     */
    public function setShippingAddress(\Magento\Customer\Api\Data\AddressInterface $shippingAddress = null);

    /**
     * Get customer tax class key
     *
     * @api
     * @return \Magento\Tax\Api\Data\TaxClassKeyInterface|null
     */
    public function getCustomerTaxClassKey();

    /**
     * Set customer tax class key
     *
     * @api
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface $customerTaxClassKey
     * @return $this
     */
    public function setCustomerTaxClassKey(\Magento\Tax\Api\Data\TaxClassKeyInterface $customerTaxClassKey = null);

    /**
     * Get customer id
     *
     * @api
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @api
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get customer data
     *
     * @api
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]|null
     */
    public function getItems();

    /**
     * Set customer data
     *
     * @api
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);

    /**
     * Get customer tax class id
     *
     * @api
     * @return int
     */
    public function getCustomerTaxClassId();

    /**
     * Set customer tax class id
     *
     * @api
     * @param int $customerTaxClassId
     * @return $this
     */
    public function setCustomerTaxClassId($customerTaxClassId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Tax\Api\Data\QuoteDetailsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Tax\Api\Data\QuoteDetailsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\QuoteDetailsExtensionInterface $extensionAttributes);
}
