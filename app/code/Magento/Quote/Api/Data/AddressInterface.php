<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * @see \Magento\Checkout\Service\V1\Data\Cart\Address
 */
interface AddressInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_EMAIL = 'email';

    const KEY_COUNTRY_ID = 'country_id';

    const KEY_ID = 'id';

    const REGION_ID = 'region_id';

    const REGION_CODE = 'region_code';

    const REGION = 'region';

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

    /**#@-*/

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get region name
     *
     * @return string
     */
    public function getRegion();

    /**
     * Get region id
     *
     * @return string
     */
    public function getRegionId();

    /**
     * Get region code
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
     * Get street
     *
     * @return string[]
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
     * @return string
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
     * Get customer id
     *
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Get billing/shipping email
     *
     * @return string
     */
    public function getEmail();
}
