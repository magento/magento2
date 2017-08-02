<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data\Quote;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class AddressAdapter
 * @since 2.0.0
 */
class AddressAdapter implements AddressAdapterInterface
{
    /**
     * @var AddressInterface
     * @since 2.0.0
     */
    private $address;

    /**
     * @param AddressInterface $address
     * @since 2.0.0
     */
    public function __construct(AddressInterface $address)
    {
        $this->address = $address;
    }

    /**
     * Get region name
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegionCode()
    {
        return $this->address->getRegionCode();
    }

    /**
     * Get country id
     *
     * @return string
     * @since 2.0.0
     */
    public function getCountryId()
    {
        return $this->address->getCountryId();
    }

    /**
     * Get street line 1
     *
     * @return string
     * @since 2.0.0
     */
    public function getStreetLine1()
    {
        $street = $this->address->getStreet();
        return isset($street[0]) ? $street[0]: '';
    }

    /**
     * Get street line 2
     *
     * @return string
     * @since 2.0.0
     */
    public function getStreetLine2()
    {
        $street = $this->address->getStreet();
        return isset($street[1]) ? $street[1]: '';
    }

    /**
     * Get telephone number
     *
     * @return string
     * @since 2.0.0
     */
    public function getTelephone()
    {
        return $this->address->getTelephone();
    }

    /**
     * Get postcode
     *
     * @return string
     * @since 2.0.0
     */
    public function getPostcode()
    {
        return $this->address->getPostcode();
    }

    /**
     * Get city name
     *
     * @return string
     * @since 2.0.0
     */
    public function getCity()
    {
        return $this->address->getCity();
    }

    /**
     * Get first name
     *
     * @return string
     * @since 2.0.0
     */
    public function getFirstname()
    {
        return $this->address->getFirstname();
    }

    /**
     * Get last name
     *
     * @return string
     * @since 2.0.0
     */
    public function getLastname()
    {
        return $this->address->getLastname();
    }

    /**
     * Get middle name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getMiddlename()
    {
        return $this->address->getMiddlename();
    }

    /**
     * Get customer id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->address->getCustomerId();
    }

    /**
     * Get billing/shipping email
     *
     * @return string
     * @since 2.0.0
     */
    public function getEmail()
    {
        return $this->address->getEmail();
    }

    /**
     * Returns name prefix
     *
     * @return string
     * @since 2.0.0
     */
    public function getPrefix()
    {
        return $this->address->getPrefix();
    }

    /**
     * Returns name suffix
     *
     * @return string
     * @since 2.0.0
     */
    public function getSuffix()
    {
        return $this->address->getSuffix();
    }

    /**
     * Get company
     *
     * @return string
     * @since 2.0.0
     */
    public function getCompany()
    {
        return $this->address->getCompany();
    }
}
