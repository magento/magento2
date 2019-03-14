<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\QuoteManagement;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\Store;

/**
 * Create Quote for instance purchase.
 *
 * @api May be used for pluginization.
 * @since 100.2.0
 */
class QuoteCreation
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * QuoteCreation constructor.
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Creates Quote for instant purchase.
     *
     * @param Store $store
     * @param Customer $customer
     * @param Address $shippingAddress
     * @param Address $billingAddress
     * @return Quote
     * @throws LocalizedException if quote can not be created.
     * @since 100.2.0
     */
    public function createQuote(
        Store $store,
        Customer $customer,
        Address $shippingAddress,
        Address $billingAddress
    ): Quote {
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($store->getId());
        $quote->setCustomer($customer->getDataModel());
        $quote->setCustomerIsGuest(0);
        $quote->getShippingAddress()
            ->importCustomerAddressData($shippingAddress->getDataModel());
        $quote->getBillingAddress()
            ->importCustomerAddressData($billingAddress->getDataModel());
        $quote->setInventoryProcessed(false);
        return $quote;
    }
}
