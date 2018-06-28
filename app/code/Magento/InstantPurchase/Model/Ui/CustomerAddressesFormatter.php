<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\Ui;

use Magento\Customer\Model\Address;

/**
 * Address string presentation.
 *
 * @api May be used for pluginization.
 */
class CustomerAddressesFormatter
{
    /**
     * Formats address to simple string.
     *
     * @param Address $address
     * @return string
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
