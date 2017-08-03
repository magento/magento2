<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Data;

/**
 * Interface AddressAdapterInterface
 * @api
 * @since 2.0.0
 */
interface AddressAdapterInterface
{
    /**
     * Get region name
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegionCode();

    /**
     * Get country id
     *
     * @return string
     * @since 2.0.0
     */
    public function getCountryId();

    /**
     * Get street line 1
     *
     * @return string
     * @since 2.0.0
     */
    public function getStreetLine1();

    /**
     * Get street line 2
     *
     * @return string
     * @since 2.0.0
     */
    public function getStreetLine2();

    /**
     * Get telephone number
     *
     * @return string
     * @since 2.0.0
     */
    public function getTelephone();

    /**
     * Get postcode
     *
     * @return string
     * @since 2.0.0
     */
    public function getPostcode();

    /**
     * Get city name
     *
     * @return string
     * @since 2.0.0
     */
    public function getCity();

    /**
     * Get first name
     *
     * @return string
     * @since 2.0.0
     */
    public function getFirstname();

    /**
     * Get last name
     *
     * @return string
     * @since 2.0.0
     */
    public function getLastname();

    /**
     * Get middle name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getMiddlename();

    /**
     * Get customer id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId();

    /**
     * Get billing/shipping email
     *
     * @return string
     * @since 2.0.0
     */
    public function getEmail();

    /**
     * Returns name prefix
     *
     * @return string
     * @since 2.0.0
     */
    public function getPrefix();

    /**
     * Returns name suffix
     *
     * @return string
     * @since 2.0.0
     */
    public function getSuffix();

    /**
     * Get company
     *
     * @return string
     * @since 2.0.0
     */
    public function getCompany();
}
