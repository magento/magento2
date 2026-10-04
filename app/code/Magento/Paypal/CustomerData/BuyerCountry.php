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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BuyerCountry implements SectionSourceInterface
{
    /**
     * @param CurrentCustomer $currentCustomer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly CurrentCustomer $currentCustomer,
        private readonly ScopeConfigInterface $scopeConfig
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
                foreach ($customer->getAddresses() as $address) {
                    if ($address->getId() == $addressId) {
                        $country = $address->getCountryId();
                        break;
                    }
                }
            }
        } catch (NoSuchEntityException $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            // ignore and fall back to store default country
        }

        // Fallback for guests or customers without default addresses:
        // use the store's default country code so Pay Later messages can render.
        if (!$country) {
            $country = (string)$this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
            $country = $country !== '' ? $country : null;
        }

        return [
            'code' => $country
        ];
    }
}
