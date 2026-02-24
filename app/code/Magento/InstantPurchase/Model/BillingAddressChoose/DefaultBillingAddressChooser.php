<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\InstantPurchase\Model\BillingAddressChoose;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address;

/**
 * Billing address chooser implementation to choose customer default billing address.
 */
class DefaultBillingAddressChooser implements BillingAddressChooserInterface
{
    /**
     * @inheritdoc
     */
    public function choose(Customer $customer)
    {
        $address = $customer->getDefaultBillingAddress();
        return $address ?: null;
    }
}
