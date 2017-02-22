<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Customer address interface.
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
     * @api
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get customer ID
     *
     * @api
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer ID
     *
     * @api
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get region
     *
     * @api
     * @return \Magento\Customer\Api\Data\RegionInterface|null
     */
    public function getRegion();

    /**
     * Set region
     *
     * @api
     * @param \Magento\Customer\Api\Data\RegionInterface $region
     * @return $this
     */
    public function setRegion(RegionInterface $region = null);

    /**
     * Get region ID
     *
     * @api
     * @return int|null
     */
    public function getRegionId();

    /**
     * Set region ID
     *
     * @api
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Two-letter country code in ISO_3166-2 format
     *
     * @api
     * @return string|null
     */
    public function getCountryId();

    /**
     * Set country id
     *
     * @api
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * Get street
     *
     * @api
     * @return string[]|null
     */
    public function getStreet();

    /**
     * Set street
     *
     * @api
     * @param string[] $street
     * @return $this
     */
    public function setStreet(array $street);

    /**
     * Get company
     *
     * @api
     * @return string|null
     */
    public function getCompany();

    /**
     * Set company
     *
     * @api
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * Get telephone number
     *
     * @api
     * @return string|null
     */
    public function getTelephone();

    /**
     * Set telephone number
     *
     * @api
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Get fax number
     *
     * @api
     * @return string|null
     */
    public function getFax();

    /**
     * Set fax number
     *
     * @api
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Get postcode
     *
     * @api
     * @return string|null
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @api
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Get city name
     *
     * @api
     * @return string|null
     */
    public function getCity();

    /**
     * Set city name
     *
     * @api
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Get first name
     *
     * @api
     * @return string|null
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @api
     * @param string $firstName
     * @return $this
     */
    public function setFirstname($firstName);

    /**
     * Get last name
     *
     * @api
     * @return string|null
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @api
     * @param string $lastName
     * @return $this
     */
    public function setLastname($lastName);

    /**
     * Get middle name
     *
     * @api
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @api
     * @param string $middleName
     * @return $this
     */
    public function setMiddlename($middleName);

    /**
     * Get prefix
     *
     * @api
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @api
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @api
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @api
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix);

    /**
     * Get Vat id
     *
     * @api
     * @return string|null
     */
    public function getVatId();

    /**
     * Set Vat id
     *
     * @api
     * @param string $vatId
     * @return $this
     */
    public function setVatId($vatId);

    /**
     * Get if this address is default shipping address.
     *
     * @api
     * @return bool|null
     */
    public function isDefaultShipping();

    /**
     * Set if this address is default shipping address.
     *
     * @api
     * @param bool $isDefaultShipping
     * @return $this
     */
    public function setIsDefaultShipping($isDefaultShipping);

    /**
     * Get if this address is default billing address
     *
     * @api
     * @return bool|null
     */
    public function isDefaultBilling();

    /**
     * Set if this address is default billing address
     *
     * @api
     * @param bool $isDefaultBilling
     * @return $this
     */
    public function setIsDefaultBilling($isDefaultBilling);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Customer\Api\Data\AddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Customer\Api\Data\AddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\AddressExtensionInterface $extensionAttributes);
}
