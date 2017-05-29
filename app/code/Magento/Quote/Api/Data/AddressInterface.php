<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface AddressInterface
 * @api
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
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get region name
     *
     * @return string
     */
    public function getRegion();

    /**
     * Set region name
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Get region id
     *
     * @return int
     */
    public function getRegionId();

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Get region code
     *
     * @return string
     */
    public function getRegionCode();

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

    /**
     * Get country id
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * Get street
     *
     * @return string[]
     */
    public function getStreet();

    /**
     * Set street
     *
     * @param string|string[] $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany();

    /**
     * Set company
     *
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * Get telephone number
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Set telephone number
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Get fax number
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Set fax number
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Get city name
     *
     * @return string
     */
    public function getCity();

    /**
     * Set city name
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename);

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this|null
     */
    public function setSuffix($suffix);

    /**
     * Get Vat id
     *
     * @return string|null
     */
    public function getVatId();

    /**
     * Set Vat id
     *
     * @param string $vatId
     * @return $this
     */
    public function setVatId($vatId);

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get billing/shipping email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set billing/shipping email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get same as billing flag
     *
     * @return int|null
     */
    public function getSameAsBilling();

    /**
     * Set same as billing flag
     *
     * @param int $sameAsBilling
     * @return $this
     */
    public function setSameAsBilling($sameAsBilling);

    /**
     * Get customer address id
     *
     * @return int|null
     */
    public function getCustomerAddressId();

    /**
     * Set customer address id
     *
     * @param int|null $customerAddressId
     * @return $this
     */
    public function setCustomerAddressId($customerAddressId);

    /**
     * Get save in address book flag
     *
     * @return int|null
     */
    public function getSaveInAddressBook();

    /**
     * Set save in address book flag
     *
     * @param int|null $saveInAddressBook
     * @return $this
     */
    public function setSaveInAddressBook($saveInAddressBook);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\AddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\AddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\AddressExtensionInterface $extensionAttributes);
}
