<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * Quote billing/shipping address data
 *
 * @codeCoverageIgnore
 */
class Address extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_EMAIL = 'email';

    const KEY_COUNTRY_ID = 'country_id';

    const KEY_ID = 'id';

    const KEY_CUSTOMER_ID = 'customer_id';

    const KEY_REGION = 'region';

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
    public function getId()
    {
        return $this->_get(self::KEY_ID);
    }

    /**
     * Get region
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Address\Region|null
     */
    public function getRegion()
    {
        return $this->_get(self::KEY_REGION);
    }

    /**
     * Get country id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->_get(self::KEY_COUNTRY_ID);
    }

    /**
     * Get street
     *
     * @return string[]
     */
    public function getStreet()
    {
        return $this->_get(self::KEY_STREET);
    }

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany()
    {
        return $this->_get(self::KEY_COMPANY);
    }

    /**
     * Get telephone number
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->_get(self::KEY_TELEPHONE);
    }

    /**
     * Get fax number
     *
     * @return string|null
     */
    public function getFax()
    {
        return $this->_get(self::KEY_FAX);
    }

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->_get(self::KEY_POSTCODE);
    }

    /**
     * Get city name
     *
     * @return string
     */
    public function getCity()
    {
        return $this->_get(self::KEY_CITY);
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->_get(self::KEY_FIRSTNAME);
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->_get(self::KEY_LASTNAME);
    }

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->_get(self::KEY_MIDDLENAME);
    }

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->_get(self::KEY_PREFIX);
    }

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->_get(self::KEY_SUFFIX);
    }

    /**
     * Get Vat id
     *
     * @return string|null
     */
    public function getVatId()
    {
        return $this->_get(self::KEY_VAT_ID);
    }

    /**
     * Get customer id
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::KEY_CUSTOMER_ID);
    }

    /**
     * Get billing/shipping email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_get(self::KEY_EMAIL);
    }
}
