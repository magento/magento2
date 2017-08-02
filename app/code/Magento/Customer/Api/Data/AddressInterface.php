<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Customer address interface.
 * @api
 * @since 2.0.0
 */
interface AddressInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const REGION = 'region';
    const REGION_ID = 'region_id';
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
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get customer ID
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId();

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerId($customerId);

    /**
     * Get region
     *
     * @return \Magento\Customer\Api\Data\RegionInterface|null
     * @since 2.0.0
     */
    public function getRegion();

    /**
     * Set region
     *
     * @param \Magento\Customer\Api\Data\RegionInterface $region
     * @return $this
     * @since 2.0.0
     */
    public function setRegion(RegionInterface $region = null);

    /**
     * Get region ID
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getRegionId();

    /**
     * Set region ID
     *
     * @param int $regionId
     * @return $this
     * @since 2.0.0
     */
    public function setRegionId($regionId);

    /**
     * Two-letter country code in ISO_3166-2 format
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCountryId();

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     * @since 2.0.0
     */
    public function setCountryId($countryId);

    /**
     * Get street
     *
     * @return string[]|null
     * @since 2.0.0
     */
    public function getStreet();

    /**
     * Set street
     *
     * @param string[] $street
     * @return $this
     * @since 2.0.0
     */
    public function setStreet(array $street);

    /**
     * Get company
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCompany();

    /**
     * Set company
     *
     * @param string $company
     * @return $this
     * @since 2.0.0
     */
    public function setCompany($company);

    /**
     * Get telephone number
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTelephone();

    /**
     * Set telephone number
     *
     * @param string $telephone
     * @return $this
     * @since 2.0.0
     */
    public function setTelephone($telephone);

    /**
     * Get fax number
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getFax();

    /**
     * Set fax number
     *
     * @param string $fax
     * @return $this
     * @since 2.0.0
     */
    public function setFax($fax);

    /**
     * Get postcode
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     * @since 2.0.0
     */
    public function setPostcode($postcode);

    /**
     * Get city name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCity();

    /**
     * Set city name
     *
     * @param string $city
     * @return $this
     * @since 2.0.0
     */
    public function setCity($city);

    /**
     * Get first name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstName
     * @return $this
     * @since 2.0.0
     */
    public function setFirstname($firstName);

    /**
     * Get last name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     * @since 2.0.0
     */
    public function setLastname($lastName);

    /**
     * Get middle name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @param string $middleName
     * @return $this
     * @since 2.0.0
     */
    public function setMiddlename($middleName);

    /**
     * Get prefix
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     * @since 2.0.0
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     * @since 2.0.0
     */
    public function setSuffix($suffix);

    /**
     * Get Vat id
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getVatId();

    /**
     * Set Vat id
     *
     * @param string $vatId
     * @return $this
     * @since 2.0.0
     */
    public function setVatId($vatId);

    /**
     * Get if this address is default shipping address.
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function isDefaultShipping();

    /**
     * Set if this address is default shipping address.
     *
     * @param bool $isDefaultShipping
     * @return $this
     * @since 2.0.0
     */
    public function setIsDefaultShipping($isDefaultShipping);

    /**
     * Get if this address is default billing address
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function isDefaultBilling();

    /**
     * Set if this address is default billing address
     *
     * @param bool $isDefaultBilling
     * @return $this
     * @since 2.0.0
     */
    public function setIsDefaultBilling($isDefaultBilling);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Customer\Api\Data\AddressExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Customer\Api\Data\AddressExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\AddressExtensionInterface $extensionAttributes);
}
