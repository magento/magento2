<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

class CustomerAddressDataProvider
{
    /**
     * @var array
     */
    private $customerAddresses = [];

    /**
     * @var CustomerAddressDataFormatter
     */
    private $customerAddressDataFormatter;

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @param CustomerAddressDataFormatter $customerAddressDataFormatter
     * @param Share|null $share
     * @param AllowedCountries|null $allowedCountryReader
     */
    public function __construct(
        CustomerAddressDataFormatter $customerAddressDataFormatter,
        ?Share $share = null,
        ?AllowedCountries $allowedCountryReader = null
    ) {
        $this->customerAddressDataFormatter = $customerAddressDataFormatter;
        $this->shareConfig = $share ?: ObjectManager::getInstance()
            ->get(Share::class);
        $this->allowedCountryReader = $allowedCountryReader ?: ObjectManager::getInstance()
            ->get(AllowedCountries::class);
    }

    /**
     * Get addresses for customer.
     *
     * @param CustomerInterface $customer
     * @param int|null $addressLimit
     * @return array
     * @throws LocalizedException
     */
    public function getAddressDataByCustomer(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        ?int $addressLimit = null
    ): array {
        if (!empty($this->customerAddresses)) {
            return $this->customerAddresses;
        }

        $customerOriginAddresses = $customer->getAddresses();
        if (!$customerOriginAddresses) {
            return [];
        }

        $allowedCountries = $this->allowedCountryReader->getAllowedCountries();
        $customerAddresses = [];
        foreach ($customerOriginAddresses as $address) {
            // Checks if a country id present in the allowed countries list.
            if ($this->shareConfig->isGlobalScope() && !in_array($address->getCountryId(), $allowedCountries)) {
                continue;
            }

            $customerAddresses[$address->getId()] = $this->customerAddressDataFormatter->prepareAddress($address);
            if ($addressLimit && count($customerAddresses) >= $addressLimit) {
                break;
            }
        }

        $this->customerAddresses = $customerAddresses;

        return $this->customerAddresses;
    }
}
