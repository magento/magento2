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
}
