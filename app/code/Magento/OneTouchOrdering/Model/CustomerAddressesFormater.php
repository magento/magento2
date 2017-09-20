<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;

/**
 * Class CustomerAddresses
 * @package Magento\OneTouchOrdering\Model
 */
class CustomerAddressesFormater
{

    public function getFormattedAddresses(Customer $customer): array
    {
        $addresses = $customer->getAddresses();
        $addressesFormatted = [];
        /**
         * @var Address $address
         */
        foreach ($addresses as $address) {
            $addressString = $this->format($address);
            $addressesFormatted[] = [
                'address' => $addressString,
                'id' => $address->getId(),
            ];
        }

        return $addressesFormatted;
    }

    private function format(Address $address)
    {
        return sprintf(
            "%s, %s, %s, %s %s, %s",
            $address->getName(),
            $address->getStreetFull(),
            $address->getCity(),
            $address->getRegion(),
            $address->getPostcode(),
            $address->getCountryModel()->getName()
        );
    }
}
