<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\InstantPurchase\Model\ShippingAddressChoose;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address;

/**
 * Shipping address chooser implementation to choose customer default shipping address.
 */
class DefaultShippingAddressChooser implements ShippingAddressChooserInterface
{
    /**
     * @inheritdoc
     */
    public function choose(Customer $customer)
    {
        $address = $customer->getDefaultShippingAddress();
        return $address ?: null;
    }
}
