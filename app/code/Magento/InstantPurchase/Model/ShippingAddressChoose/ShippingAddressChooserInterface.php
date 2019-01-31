<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\ShippingAddressChoose;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address;

/**
 * Interface to choose shipping address for a customer if available.
 *
 * @api
 */
interface ShippingAddressChooserInterface
{
    /**
     * @param Customer $customer
     * @return Address|null
     */
    public function choose(Customer $customer);
}
