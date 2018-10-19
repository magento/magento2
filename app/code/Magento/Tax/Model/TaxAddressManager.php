<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\Session;
use Magento\Tax\Api\TaxAddressManagerInterface;

/**
 * Class to save address in customer session.
 */
class TaxAddressManager implements TaxAddressManagerInterface
{
    /**
     * Customer session model.
     *
     * @var Session
     */
    private $customerSession;

    /**
     * @param Session $customerSession
     */
    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Set default Tax Billing and Shipping address into customer session after address save.
     *
     * @param Address $address
     * @return void
     */
    public function setDefaultAddressAfterSave(Address $address)
    {
        if ($this->isDefaultBilling($address)) {
            $this->customerSession->setDefaultTaxBillingAddress(
                [
                    'country_id' => $address->getCountryId(),
                    'region_id' => $address->getRegion() ? $address->getRegionId() : null,
                    'postcode' => $address->getPostcode(),
                ]
            );
        }
        if ($this->isDefaultShipping($address)) {
            $this->customerSession->setDefaultTaxShippingAddress(
                [
                    'country_id' => $address->getCountryId(),
                    'region_id' => $address->getRegion() ? $address->getRegionId() : null,
                    'postcode' => $address->getPostcode(),
                ]
            );
        }
    }

    /**
     * Set default Tax Shipping and Billing addresses into customer session after login.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface[] $addresses
     * @return void
     */
    public function setDefaultAddressAfterLogIn(array $addresses)
    {
        $defaultShippingFound = false;
        $defaultBillingFound = false;
        foreach ($addresses as $address) {
            if ($address->isDefaultBilling()) {
                $defaultBillingFound = true;
                $this->customerSession->setDefaultTaxBillingAddress(
                    [
                        'country_id' => $address->getCountryId(),
                        'region_id' => $address->getRegion() ? $address->getRegionId() : null,
                        'postcode' => $address->getPostcode(),
                    ]
                );
            }
            if ($address->isDefaultShipping()) {
                $defaultShippingFound = true;
                $this->customerSession->setDefaultTaxShippingAddress(
                    [
                        'country_id' => $address->getCountryId(),
                        'region_id' => $address->getRegion() ? $address->getRegionId() : null,
                        'postcode' => $address->getPostcode(),
                    ]
                );
            }
            if ($defaultShippingFound && $defaultBillingFound) {
                break;
            }
        }
    }

    /**
     * Check whether specified billing address is default for customer from address.
     *
     * @param Address $address
     * @return bool
     */
    private function isDefaultBilling(Address $address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultBilling()
            || $address->getIsPrimaryBilling()
            || $address->getIsDefaultBilling();
    }

    /**
     * Check whether specified shipping address is default for customer from address.
     *
     * @param Address $address
     * @return bool
     */
    private function isDefaultShipping(Address $address)
    {
        return $address->getId() && $address->getId() == $address->getCustomer()->getDefaultShipping()
            || $address->getIsPrimaryShipping()
            || $address->getIsDefaultShipping();
    }
}
