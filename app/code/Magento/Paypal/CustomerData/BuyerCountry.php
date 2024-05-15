<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Paypal\CustomerData;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;

class BuyerCountry implements SectionSourceInterface
{
    /**
     * @param CurrentCustomer $currentCustomer
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        private readonly CurrentCustomer $currentCustomer,
        private readonly AddressRepositoryInterface $addressRepository
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $country = null;
        try {
            $customer = $this->currentCustomer->getCustomer();
            $addressId = $customer->getDefaultBilling() ?
                $customer->getDefaultBilling() :
                $customer->getDefaultShipping();

            if ($addressId) {
                $address = $this->addressRepository->getById($addressId);
                $country = $address->getCountryId();
            }
        } catch (NoSuchEntityException $e) {
        }

        return [
            'code' => $country
        ];
    }
}
