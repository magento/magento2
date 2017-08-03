<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface AddressInterface
 * @api
 * @since 2.0.0
 */
interface AddressInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_EMAIL = 'email';

    const KEY_COUNTRY_ID = 'country_id';

    const KEY_ID = 'id';

    const KEY_REGION_ID = 'region_id';

    const KEY_REGION_CODE = 'region_code';

    const KEY_REGION = 'region';

    const KEY_CUSTOMER_ID = 'customer_id';

    const KEY_STREET = 'street';

    const KEY_COMPANY = 'company';

    const KEY_TELEPHONE = 'telephone';

    const KEY_FAX = 'fax';

    const KEY_POSTCODE = 'postcode';

    const KEY_CITY = 'city';

    const KEY_FIRSTNAME = 'firstname';

    const KEY_LASTNAME = 'lastname';

    const KEY_MIDDLENAME = 'middlename';

    const KEY_PREFIX = 'prefix';

    const KEY_SUFFIX = 'suffix';

    const KEY_VAT_ID = 'vat_id';

    const SAME_AS_BILLING = 'same_as_billing';

    const CUSTOMER_ADDRESS_ID = 'customer_address_id';

    const SAVE_IN_ADDRESS_BOOK = 'save_in_address_book';

    /**#@-*/

    /**
     * Get id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get region name
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegion();

    /**
     * Set region name
     *
     * @param string $region
     * @return $this
     * @since 2.0.0
     */
    public function setRegion($region);

    /**
     * Get region id
     *
     * @return int
     * @since 2.0.0
     */
    public function getRegionId();

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     * @since 2.0.0
     */
    public function setRegionId($regionId);

    /**
     * Get region code
     *
     * @return string
     * @since 2.0.0
     */
    public function getRegionCode();

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     * @since 2.0.0
     */
    public function setRegionCode($regionCode);

    /**
     * Get country id
     *
     * @return string
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
     * @return string[]
     * @since 2.0.0
     */
    public function getStreet();

    /**
     * Set street
     *
     * @param string|string[] $street
     * @return $this
     * @since 2.0.0
     */
    public function setStreet($street);

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
     * @return string
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
     * @return string
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
     * @return string
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
     * @return string
     * @since 2.0.0
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     * @since 2.0.0
     */
    public function setFirstname($firstname);

    /**
     * Get last name
     *
     * @return string
     * @since 2.0.0
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     * @since 2.0.0
     */
    public function setLastname($lastname);

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
     * @param string $middlename
     * @return $this
     * @since 2.0.0
     */
    public function setMiddlename($middlename);

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
     * @return $this|null
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
     * Get customer id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerId($customerId);

    /**
     * Get billing/shipping email
     *
     * @return string
     * @since 2.0.0
     */
    public function getEmail();

    /**
     * Set billing/shipping email
     *
     * @param string $email
     * @return $this
     * @since 2.0.0
     */
    public function setEmail($email);

    /**
     * Get same as billing flag
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getSameAsBilling();

    /**
     * Set same as billing flag
     *
     * @param int $sameAsBilling
     * @return $this
     * @since 2.0.0
     */
    public function setSameAsBilling($sameAsBilling);

    /**
     * Get customer address id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerAddressId();

    /**
     * Set customer address id
     *
     * @param int|null $customerAddressId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerAddressId($customerAddressId);

    /**
     * Get save in address book flag
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getSaveInAddressBook();

    /**
     * Set save in address book flag
     *
     * @param int|null $saveInAddressBook
     * @return $this
     * @since 2.0.0
     */
    public function setSaveInAddressBook($saveInAddressBook);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\AddressExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\AddressExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\AddressExtensionInterface $extensionAttributes);
}
