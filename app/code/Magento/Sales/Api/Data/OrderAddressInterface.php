<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Order address interface.
 *
 * An order is a document that a web store issues to a customer. Magento generates a sales order that lists the product
 * items, billing and shipping addresses, and shipping and payment methods. A corresponding external document, known as
 * a purchase order, is emailed to the customer.
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
     * Quote address ID.
     */
    const QUOTE_ADDRESS_ID = 'quote_address_id';
    /*
     * Region ID.
     */
    const REGION_ID = 'region_id';
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
     * @return string Company.
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
     * @return int Country address ID.
     */
    public function getCustomerAddressId();

    /**
     * Gets the customer ID for the order address.
     *
     * @return int Customer ID.
     */
    public function getCustomerId();

    /**
     * Gets the email address for the order address.
     *
     * @return string Email address.
     */
    public function getEmail();

    /**
     * Gets the ID for the order address.
     *
     * @return int Order address ID.
     */
    public function getEntityId();

    /**
     * Gets the fax number for the order address.
     *
     * @return string Fax number.
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
     * @return string Middle name.
     */
    public function getMiddlename();

    /**
     * Gets the parent ID for the order address.
     *
     * @return int Parent ID.
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
     * @return string Prefix.
     */
    public function getPrefix();

    /**
     * Gets the quote address ID for the order address.
     *
     * @return int Quote address ID.
     */
    public function getQuoteAddressId();

    /**
     * Gets the region for the order address.
     *
     * @return string Region.
     */
    public function getRegion();

    /**
     * Gets the region ID for the order address.
     *
     * @return int Region ID.
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
     * @return string Suffix.
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
     * @return string VAT ID.
     */
    public function getVatId();

    /**
     * Gets the VAT-is-valid flag value for the order address.
     *
     * @return int VAT-is-valid flag value.
     */
    public function getVatIsValid();

    /**
     * Gets the VAT request date for the order address.
     *
     * @return string VAT request date.
     */
    public function getVatRequestDate();

    /**
     * Gets the VAT request ID for the order address.
     *
     * @return string VAT request ID.
     */
    public function getVatRequestId();

    /**
     * Gets the VAT-request-success flag value for the order address.
     *
     * @return int VAT-request-success flag value.
     */
    public function getVatRequestSuccess();
}
