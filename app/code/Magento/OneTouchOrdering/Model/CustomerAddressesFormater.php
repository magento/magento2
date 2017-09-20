<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Customer\Model\Customer;

/**
 * Class CustomerAddresses
 * @package Magento\OneTouchOrdering\Model
 */
class CustomerAddresses
{

    public function getFormattedAddresses(Customer $customer): array
    {
        $addresses = $customer->getAddresses();
        $addressesFormatted = [];
        /**
         * @var \Magento\Customer\Model\Address $address
         */
        foreach ($addresses as $address) {
            $addressString = sprintf(
                "%s, %s, %s, %s %s, %s",
                $address->getName(),
                $address->getStreetFull(),
                $address->getCity(),
                $address->getRegion(),
                $address->getPostcode(),
                $address->getCountryModel()->getName()
            );
            $addressesFormatted[] = [
                'address' => $addressString,
                'id' => $address->getId(),
            ];
        }

        return $addressesFormatted;
    }
}
