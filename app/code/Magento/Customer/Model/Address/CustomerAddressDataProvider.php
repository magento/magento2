<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

/**
 * Provides customer address data.
 */
class CustomerAddressDataProvider
{
    /**
     * Customer addresses.
     *
     * @var array
     */
    private $customerAddresses = [];

    /**
     * @var CustomerAddressDataFormatter
     */
    private $customerAddressDataFormatter;

    /**
     * @param CustomerAddressDataFormatter $customerAddressDataFormatter
     */
    public function __construct(
        CustomerAddressDataFormatter $customerAddressDataFormatter
    ) {
        $this->customerAddressDataFormatter = $customerAddressDataFormatter;
    }

    /**
     * Get addresses for customer.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressDataByCustomer(
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ): array {
        if (!empty($this->customerAddresses)) {
            return $this->customerAddresses;
        }

        $customerOriginAddresses = $customer->getAddresses();
        if (!$customerOriginAddresses) {
            return [];
        }

        $customerAddresses = [];
        foreach ($customerOriginAddresses as $address) {
            $customerAddresses[$address->getId()] = $this->customerAddressDataFormatter->prepareAddress($address);
        }

        $this->customerAddresses = $customerAddresses;

        return $this->customerAddresses;
    }
}
