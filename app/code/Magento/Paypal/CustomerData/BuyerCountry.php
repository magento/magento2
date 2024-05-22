<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Paypal\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;

class BuyerCountry implements SectionSourceInterface
{
    /**
     * @param CurrentCustomer $currentCustomer
     */
    public function __construct(private readonly CurrentCustomer $currentCustomer)
    {
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
                foreach ($customer->getAddresses() as $address) {
                    if ($address->getId() == $addressId) {
                        $country = $address->getCountryId();
                        break;
                    }
                }
            }
        } catch (NoSuchEntityException $e) {
            return [
                'code' => null
            ];
        }

        return [
            'code' => $country
        ];
    }
}
