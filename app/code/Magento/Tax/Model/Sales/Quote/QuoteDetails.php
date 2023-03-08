<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Quote;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\QuoteDetailsExtensionInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;

/**
 * @codeCoverageIgnore
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
     */
    public function getBillingAddress()
    {
        return $this->getData(self::KEY_BILLING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress()
    {
        return $this->getData(self::KEY_SHIPPING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerTaxClassKey()
    {
        return $this->getData(self::KEY_CUSTOMER_TAX_CLASS_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::KEY_CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->getData(self::KEY_ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerTaxClassId()
    {
        return $this->getData(self::KEY_CUSTOMER_TAX_CLASS_ID);
    }

    /**
     * Set customer billing address
     *
     * @param AddressInterface $billingAddress
     * @return $this
     */
    public function setBillingAddress(AddressInterface $billingAddress = null)
    {
        return $this->setData(self::KEY_BILLING_ADDRESS, $billingAddress);
    }

    /**
     * Set customer shipping address
     *
     * @param AddressInterface $shippingAddress
     * @return $this
     */
    public function setShippingAddress(AddressInterface $shippingAddress = null)
    {
        return $this->setData(self::KEY_SHIPPING_ADDRESS, $shippingAddress);
    }

    /**
     * Set customer tax class key
     *
     * @param TaxClassKeyInterface $customerTaxClassKey
     * @return $this
     */
    public function setCustomerTaxClassKey(TaxClassKeyInterface $customerTaxClassKey = null)
    {
        return $this->setData(self::KEY_CUSTOMER_TAX_CLASS_KEY, $customerTaxClassKey);
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::KEY_CUSTOMER_ID, $customerId);
    }

    /**
     * Set customer data
     *
     * @param QuoteDetailsItemInterface[] $items
     * @return $this
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
     */
    public function setCustomerTaxClassId($customerTaxClassId)
    {
        return $this->setData(self::KEY_CUSTOMER_TAX_CLASS_ID, $customerTaxClassId);
    }

    /**
     * {@inheritdoc}
     *
     * @return QuoteDetailsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param QuoteDetailsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(QuoteDetailsExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
