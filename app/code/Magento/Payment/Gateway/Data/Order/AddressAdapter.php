<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Class AddressAdapter
 */
class AddressAdapter implements AddressAdapterInterface
{
    /**
     * @var OrderAddressInterface
     */
    private $address;

    /**
     * @param OrderAddressInterface $address
     */
    public function __construct(OrderAddressInterface $address)
    {
        $this->address = $address;
    }

    /**
     * Get region name
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->address->getRegionCode();
    }

    /**
     * Get country id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->address->getCountryId();
    }

    /**
     * Get street line 1
     *
     * @return string
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
     */
    public function getTelephone()
    {
        return $this->address->getTelephone();
    }

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->address->getPostcode();
    }

    /**
     * Get city name
     *
     * @return string
     */
    public function getCity()
    {
        return $this->address->getCity();
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->address->getFirstname();
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->address->getLastname();
    }

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->address->getMiddlename();
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->address->getCustomerId();
    }

    /**
     * Get billing/shipping email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->address->getEmail();
    }

    /**
     * Returns name prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->address->getPrefix();
    }

    /**
     * Returns name suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->address->getSuffix();
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->address->getCompany();
    }
}
