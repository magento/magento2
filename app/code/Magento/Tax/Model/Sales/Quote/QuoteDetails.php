<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Quote;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\QuoteDetailsInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class QuoteDetails extends AbstractExtensibleModel implements QuoteDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_BILLING_ADDRESS        = 'billing_address';
    const KEY_SHIPPING_ADDRESS       = 'shipping_address';
    const KEY_CUSTOMER_TAX_CLASS_KEY = 'customer_tax_class_key';
    const KEY_ITEMS                  = 'items';
    const KEY_CUSTOMER_TAX_CLASS_ID  = 'customer_tax_class_id';
    const KEY_CUSTOMER_ID            = 'customer_id';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBillingAddress()
    {
        return $this->getData(self::KEY_BILLING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getShippingAddress()
    {
        return $this->getData(self::KEY_SHIPPING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomerTaxClassKey()
    {
        return $this->getData(self::KEY_CUSTOMER_TAX_CLASS_KEY);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->getData(self::KEY_CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->getData(self::KEY_ITEMS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomerTaxClassId()
    {
        return $this->getData(self::KEY_CUSTOMER_TAX_CLASS_ID);
    }

    /**
     * Set customer billing address
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $billingAddress
     * @return $this
     * @since 2.0.0
     */
    public function setBillingAddress(\Magento\Customer\Api\Data\AddressInterface $billingAddress = null)
    {
        return $this->setData(self::KEY_BILLING_ADDRESS, $billingAddress);
    }

    /**
     * Set customer shipping address
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $shippingAddress
     * @return $this
     * @since 2.0.0
     */
    public function setShippingAddress(\Magento\Customer\Api\Data\AddressInterface $shippingAddress = null)
    {
        return $this->setData(self::KEY_SHIPPING_ADDRESS, $shippingAddress);
    }

    /**
     * Set customer tax class key
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface $customerTaxClassKey
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerTaxClassKey(\Magento\Tax\Api\Data\TaxClassKeyInterface $customerTaxClassKey = null)
    {
        return $this->setData(self::KEY_CUSTOMER_TAX_CLASS_KEY, $customerTaxClassKey);
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::KEY_CUSTOMER_ID, $customerId);
    }

    /**
     * Set customer data
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * Set customer tax class id
     *
     * @param int $customerTaxClassId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerTaxClassId($customerTaxClassId)
    {
        return $this->setData(self::KEY_CUSTOMER_TAX_CLASS_ID, $customerTaxClassId);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\QuoteDetailsExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\QuoteDetailsExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
