<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Quote;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\QuoteDetailsInterface;

/**
 * @codeCoverageIgnore
 */
class QuoteDetails extends AbstractExtensibleModel implements QuoteDetailsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        return $this->getData(QuoteDetailsInterface::KEY_BILLING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress()
    {
        return $this->getData(QuoteDetailsInterface::KEY_SHIPPING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerTaxClassKey()
    {
        return $this->getData(QuoteDetailsInterface::KEY_CUSTOMER_TAX_CLASS_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(QuoteDetailsInterface::KEY_CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->getData(QuoteDetailsInterface::KEY_ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerTaxClassId()
    {
        return $this->getData(QuoteDetailsInterface::CUSTOMER_TAX_CLASS_ID);
    }

    /**
     * Set customer billing address
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $billingAddress
     * @return $this
     */
    public function setBillingAddress(\Magento\Customer\Api\Data\AddressInterface $billingAddress = null)
    {
        return $this->setData(QuoteDetailsInterface::KEY_BILLING_ADDRESS, $billingAddress);
    }

    /**
     * Set customer shipping address
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $shippingAddress
     * @return $this
     */
    public function setShippingAddress(\Magento\Customer\Api\Data\AddressInterface $shippingAddress = null)
    {
        return $this->setData(QuoteDetailsInterface::KEY_SHIPPING_ADDRESS, $shippingAddress);
    }

    /**
     * Set customer tax class key
     *
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterface $customerTaxClassKey
     * @return $this
     */
    public function setCustomerTaxClassKey(\Magento\Tax\Api\Data\TaxClassKeyInterface $customerTaxClassKey = null)
    {
        return $this->setData(QuoteDetailsInterface::KEY_CUSTOMER_TAX_CLASS_KEY, $customerTaxClassKey);
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(QuoteDetailsInterface::KEY_CUSTOMER_ID, $customerId);
    }

    /**
     * Set customer data
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this->setData(QuoteDetailsInterface::KEY_ITEMS, $items);
    }

    /**
     * Set customer tax class id
     *
     * @param int $customerTaxClassId
     * @return $this
     */
    public function setCustomerTaxClassId($customerTaxClassId)
    {
        return $this->setData(QuoteDetailsInterface::CUSTOMER_TAX_CLASS_ID, $customerTaxClassId);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\QuoteDetailsExtensionInterface|null
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
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\QuoteDetailsExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
