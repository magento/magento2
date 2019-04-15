<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Prepare address data
 */
class AddressBuilder
{
    /**
     * Returns address data params based on OrderAddressInterface
     *
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

        return $lines[$number - 1] ?? '';
    }
}
