<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleExtensionAttributes\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer address interface.
 */
interface FakeAddressInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const REGION = 'region';
    const REGIONS = 'regions';
    const COUNTRY_ID = 'country_id';
    const STREET = 'street';
    const COMPANY = 'company';
    const TELEPHONE = 'telephone';
    const FAX = 'fax';
    const POSTCODE = 'postcode';
    const CITY = 'city';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const MIDDLENAME = 'middlename';
    const PREFIX = 'prefix';
    const SUFFIX = 'suffix';
    const VAT_ID = 'vat_id';
    const DEFAULT_BILLING = 'default_billing';
    const DEFAULT_SHIPPING = 'default_shipping';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get region
     *
     * @return \Magento\TestModuleExtensionAttributes\Api\Data\FakeRegionInterface|null
     */
    public function getRegion();

    /**
     * Get region
     *
     * @return \Magento\TestModuleExtensionAttributes\Api\Data\FakeRegionInterface[]|null
     */
    public function getRegions();

    /**
     * Two-letter country code in ISO_3166-2 format
     *
     * @return string|null
     */
    public function getCountryId();

    /**
     * Get street
     *
     * @return string[]|null
     */
    public function getStreet();

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany();

    /**
     * Get telephone number
     *
     * @return string|null
     */
    public function getTelephone();

    /**
     * Get fax number
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Get postcode
     *
     * @return string|null
     */
    public function getPostcode();

    /**
     * Get city name
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstname();

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastname();

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Get Vat id
     *
     * @return string|null
     */
    public function getVatId();

    /**
     * Get if this address is default shipping address.
     *
     * @return bool|null
     */
    public function isDefaultShipping();

    /**
     * Get if this address is default billing address
     *
     * @return bool|null
     */
    public function isDefaultBilling();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\TestModuleExtensionAttributes\Api\Data\FakeAddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\TestModuleExtensionAttributes\Api\Data\FakeAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\TestModuleExtensionAttributes\Api\Data\FakeAddressExtensionInterface $extensionAttributes
    );
}
