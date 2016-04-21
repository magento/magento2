<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data;

/**
 * Interface AddressAdapterInterface
 * @api
 */
interface AddressAdapterInterface
{
    /**
     * Get region name
     *
     * @return string
     */
    public function getRegionCode();

    /**
     * Get country id
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Get street line 1
     *
     * @return string
     */
    public function getStreetLine1();

    /**
     * Get street line 2
     *
     * @return string
     */
    public function getStreetLine2();

    /**
     * Get telephone number
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Get city name
     *
     * @return string
     */
    public function getCity();

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get billing/shipping email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Returns name prefix
     *
     * @return string
     */
    public function getPrefix();

    /**
     * Returns name suffix
     *
     * @return string
     */
    public function getSuffix();

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany();
}
