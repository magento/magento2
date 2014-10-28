<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Service\V1\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObject as DataObject;

/**
 * Class OrderAddress
 */
class OrderAddress extends DataObject
{
    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
    const QUOTE_ADDRESS_ID = 'quote_address_id';
    const REGION_ID = 'region_id';
    const CUSTOMER_ID = 'customer_id';
    const FAX = 'fax';
    const REGION = 'region';
    const POSTCODE = 'postcode';
    const LASTNAME = 'lastname';
    const STREET = 'street';
    const CITY = 'city';
    const EMAIL = 'email';
    const TELEPHONE = 'telephone';
    const COUNTRY_ID = 'country_id';
    const FIRSTNAME = 'firstname';
    const ADDRESS_TYPE = 'address_type';
    const PREFIX = 'prefix';
    const MIDDLENAME = 'middlename';
    const SUFFIX = 'suffix';
    const COMPANY = 'company';
    const VAT_ID = 'vat_id';
    const VAT_IS_VALID = 'vat_is_valid';
    const VAT_REQUEST_ID = 'vat_request_id';
    const VAT_REQUEST_DATE = 'vat_request_date';
    const VAT_REQUEST_SUCCESS = 'vat_request_success';

    /**
     * Returns address_type
     *
     * @return string
     */
    public function getAddressType()
    {
        return $this->_get(self::ADDRESS_TYPE);
    }

    /**
     * Returns city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->_get(self::CITY);
    }

    /**
     * Returns company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->_get(self::COMPANY);
    }

    /**
     * Returns country_id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->_get(self::COUNTRY_ID);
    }

    /**
     * Returns customer_address_id
     *
     * @return int
     */
    public function getCustomerAddressId()
    {
        return $this->_get(self::CUSTOMER_ADDRESS_ID);
    }

    /**
     * Returns customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Returns email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Returns fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->_get(self::FAX);
    }

    /**
     * Returns firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * Returns lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * Returns middlename
     *
     * @return string
     */
    public function getMiddlename()
    {
        return $this->_get(self::MIDDLENAME);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->_get(self::PARENT_ID);
    }

    /**
     * Returns postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->_get(self::POSTCODE);
    }

    /**
     * Returns prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->_get(self::PREFIX);
    }

    /**
     * Returns quote_address_id
     *
     * @return int
     */
    public function getQuoteAddressId()
    {
        return $this->_get(self::QUOTE_ADDRESS_ID);
    }

    /**
     * Returns region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->_get(self::REGION);
    }

    /**
     * Returns region_id
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * Returns street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->_get(self::STREET);
    }

    /**
     * Returns suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->_get(self::SUFFIX);
    }

    /**
     * Returns telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->_get(self::TELEPHONE);
    }

    /**
     * Returns vat_id
     *
     * @return string
     */
    public function getVatId()
    {
        return $this->_get(self::VAT_ID);
    }

    /**
     * Returns vat_is_valid
     *
     * @return int
     */
    public function getVatIsValid()
    {
        return $this->_get(self::VAT_IS_VALID);
    }

    /**
     * Returns vat_request_date
     *
     * @return string
     */
    public function getVatRequestDate()
    {
        return $this->_get(self::VAT_REQUEST_DATE);
    }

    /**
     * Returns vat_request_id
     *
     * @return string
     */
    public function getVatRequestId()
    {
        return $this->_get(self::VAT_REQUEST_ID);
    }

    /**
     * Returns vat_request_success
     *
     * @return int
     */
    public function getVatRequestSuccess()
    {
        return $this->_get(self::VAT_REQUEST_SUCCESS);
    }
}
