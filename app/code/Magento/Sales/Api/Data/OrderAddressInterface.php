<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order address interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
 * @api
 */
interface OrderAddressInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Parent ID.
     */
    const PARENT_ID = 'parent_id';
    /*
     * Customer address ID.
     */
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
    /*
     * Region ID.
     */
    const REGION_ID = 'region_id';
    /**
     * Region code.
     */
    const KEY_REGION_CODE = 'region_code';
    /*
     * Customer ID.
     */
    const CUSTOMER_ID  = 'customer_id';
    /*
     * Fax.
     */
    const FAX = 'fax';
    /*
     * Region.
     */
    const REGION = 'region';
    /*
     * Postal code.
     */
    const POSTCODE = 'postcode';
    /*
     * Last name.
     */
    const LASTNAME = 'lastname';
    /*
     * Street.
     */
    const STREET = 'street';
    /*
     * City.
     */
    const CITY = 'city';
    /*
     * Email address.
     */
    const EMAIL = 'email';
    /*
     * Telephone number.
     */
    const TELEPHONE = 'telephone';
    /*
     * Country ID.
     */
    const COUNTRY_ID = 'country_id';
    /*
     * First name.
     */
    const FIRSTNAME = 'firstname';
    /*
     * Address type.
     */
    const ADDRESS_TYPE = 'address_type';
    /*
     * Prefix.
     */
    const PREFIX = 'prefix';
    /*
     * Middle name.
     */
    const MIDDLENAME = 'middlename';
    /*
     * Suffix.
     */
    const SUFFIX = 'suffix';
    /*
     * Company.
     */
    const COMPANY = 'company';
    /*
     * Value-added tax (VAT) ID.
     */
    const VAT_ID = 'vat_id';
    /*
     * VAT-is-valid flag.
     */
    const VAT_IS_VALID = 'vat_is_valid';
    /*
     * VAT request ID.
     */
    const VAT_REQUEST_ID = 'vat_request_id';
    /*
     * VAT request date.
     */
    const VAT_REQUEST_DATE = 'vat_request_date';
    /*
     * VAT-request-success flag.
     */
    const VAT_REQUEST_SUCCESS = 'vat_request_success';

    /**
     * Gets the address type for the order address.
     *
     * @return string Address type.
     */
    public function getAddressType();

    /**
     * Gets the city for the order address.
     *
     * @return string City.
     */
    public function getCity();

    /**
     * Gets the company for the order address.
     *
     * @return string|null Company.
     */
    public function getCompany();

    /**
     * Gets the country ID for the order address.
     *
     * @return string Country ID.
     */
    public function getCountryId();

    /**
     * Gets the country address ID for the order address.
     *
     * @return int|null Country address ID.
     */
    public function getCustomerAddressId();

    /**
     * Gets the customer ID for the order address.
     *
     * @return int|null Customer ID.
     */
    public function getCustomerId();

    /**
     * Gets the email address for the order address.
     *
     * @return string|null Email address.
     */
    public function getEmail();

    /**
     * Gets the ID for the order address.
     *
     * @return int|null Order address ID.
     */
    public function getEntityId();

    /**
     * Sets the ID for the order address.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Gets the fax number for the order address.
     *
     * @return string|null Fax number.
     */
    public function getFax();

    /**
     * Gets the first name for the order address.
     *
     * @return string First name.
     */
    public function getFirstname();

    /**
     * Gets the last name for the order address.
     *
     * @return string Last name.
     */
    public function getLastname();

    /**
     * Gets the middle name for the order address.
     *
     * @return string|null Middle name.
     */
    public function getMiddlename();

    /**
     * Gets the parent ID for the order address.
     *
     * @return int|null Parent ID.
     */
    public function getParentId();

    /**
     * Gets the postal code for the order address.
     *
     * @return string Postal code.
     */
    public function getPostcode();

    /**
     * Gets the prefix for the order address.
     *
     * @return string|null Prefix.
     */
    public function getPrefix();

    /**
     * Gets the region for the order address.
     *
     * @return string|null Region.
     */
    public function getRegion();

    /**
     * Gets the region code for the order address
     *
     * @return string|null Region code.
     */
    public function getRegionCode();

    /**
     * Gets the region ID for the order address.
     *
     * @return int|null Region ID.
     */
    public function getRegionId();

    /**
     * Gets the street values, if any, for the order address.
     *
     * @return string[]|null Array of any street values. Otherwise, null.
     */
    public function getStreet();

    /**
     * Gets the suffix for the order address.
     *
     * @return string|null Suffix.
     */
    public function getSuffix();

    /**
     * Gets the telephone number for the order address.
     *
     * @return string Telephone number.
     */
    public function getTelephone();

    /**
     * Gets the VAT ID for the order address.
     *
     * @return string|null VAT ID.
     */
    public function getVatId();

    /**
     * Gets the VAT-is-valid flag value for the order address.
     *
     * @return int|null VAT-is-valid flag value.
     */
    public function getVatIsValid();

    /**
     * Gets the VAT request date for the order address.
     *
     * @return string|null VAT request date.
     */
    public function getVatRequestDate();

    /**
     * Gets the VAT request ID for the order address.
     *
     * @return string|null VAT request ID.
     */
    public function getVatRequestId();

    /**
     * Gets the VAT-request-success flag value for the order address.
     *
     * @return int|null VAT-request-success flag value.
     */
    public function getVatRequestSuccess();

    /**
     * Sets the parent ID for the order address.
     *
     * @param int $id
     * @return $this
     */
    public function setParentId($id);

    /**
     * Sets the country address ID for the order address.
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerAddressId($id);

    /**
     * Sets the region ID for the order address.
     *
     * @param int $id
     * @return $this
     */
    public function setRegionId($id);

    /**
     * Sets the customer ID for the order address.
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerId($id);

    /**
     * Sets the fax number for the order address.
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Sets the region for the order address.
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Sets the postal code for the order address.
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Sets the last name for the order address.
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Sets the street values, if any, for the order address.
     *
     * @param string|string[] $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * Sets the city for the order address.
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Sets the email address for the order address.
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Sets the telephone number for the order address.
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Sets the country ID for the order address.
     *
     * @param string $id
     * @return $this
     */
    public function setCountryId($id);

    /**
     * Sets the first name for the order address.
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * Sets the address type for the order address.
     *
     * @param string $addressType
     * @return $this
     */
    public function setAddressType($addressType);

    /**
     * Sets the prefix for the order address.
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Sets the middle name for the order address.
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename);

    /**
     * Sets the suffix for the order address.
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix);

    /**
     * Sets the company for the order address.
     *
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * Sets the VAT ID for the order address.
     *
     * @param string $id
     * @return $this
     */
    public function setVatId($id);

    /**
     * Sets the VAT-is-valid flag value for the order address.
     *
     * @param int $vatIsValid
     * @return $this
     */
    public function setVatIsValid($vatIsValid);

    /**
     * Sets the VAT request ID for the order address.
     *
     * @param string $id
     * @return $this
     */
    public function setVatRequestId($id);

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

    /**
     * Sets the VAT request date for the order address.
     *
     * @param string $vatRequestDate
     * @return $this
     */
    public function setVatRequestDate($vatRequestDate);

    /**
     * Sets the VAT-request-success flag value for the order address.
     *
     * @param int $vatRequestSuccess
     * @return $this
     */
    public function setVatRequestSuccess($vatRequestSuccess);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Sales\Api\Data\OrderAddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Sales\Api\Data\OrderAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\OrderAddressExtensionInterface $extensionAttributes);
}
