<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\InstantPurchase\Model\BillingAddressChoose;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address;

/**
 * Interface to choose billing address for a customer if available.
 *
 * @api
 * @since 100.2.0
 */
interface BillingAddressChooserInterface
{
    /**
     * @param Customer $customer
     * @return Address|null
     * @since 100.2.0
     */
    public function choose(Customer $customer);
}
