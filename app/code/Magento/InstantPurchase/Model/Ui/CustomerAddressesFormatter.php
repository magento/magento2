<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\InstantPurchase\Model\Ui;

use Magento\Customer\Model\Address;

/**
 * Address string presentation.
 *
 * @api May be used for pluginization.
 * @since 100.2.0
 */
class CustomerAddressesFormatter
{
    /**
     * Formats address to simple string.
     *
     * @param Address $address
     * @return string
     * @since 100.2.0
     */
    public function format(Address $address): string
    {
        return sprintf(
            '%s, %s, %s, %s %s, %s',
            $address->getName(),
            $address->getStreetFull(),
            $address->getCity(),
            $address->getRegion(),
            $address->getPostcode(),
            $address->getCountryModel()->getName()
        );
    }
}
