<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\BillingAddressChoose;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address;

/**
 * Interface to choose billing address for a customer if available.
 *
 * @api
 */
interface BillingAddressChooserInterface
{
    /**
     * @param Customer $customer
     * @return Address|null
     */
    public function choose(Customer $customer);
}
