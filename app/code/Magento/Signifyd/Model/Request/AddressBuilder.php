<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Request;

use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Prepare address data
 */
class AddressBuilder
{
    /**
     * @param OrderAddressInterface $address
     * @return array
     */
    public function build(OrderAddressInterface $address)
    {
        return [
            'streetAddress' => $this->getStreetLine(1, $address->getStreet()),
            'unit' => $this->getStreetLine(2, $address->getStreet()),
            'city' => $address->getCity(),
            'provinceCode' => $address->getRegionCode(),
            'postalCode' => $address->getPostcode(),
            'countryCode' => $address->getCountryId()
        ];
    }

    /**
     * Get street line by number
     *
     * @param int $number
     * @param string[]|null $street
     * @return string
     */
    private function getStreetLine($number, $street)
    {
        $lines = is_array($street) ? $street : [];

        return isset($lines[$number - 1]) ? $lines[$number - 1] : '';
    }
}
