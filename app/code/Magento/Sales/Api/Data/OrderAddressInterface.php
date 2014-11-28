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
namespace Magento\Sales\Api\Data;

/**
 * Interface OrderAddressInterface
 */
interface OrderAddressInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
    public function getAddressType();

    /**
     * Returns city
     *
     * @return string
     */
    public function getCity();

    /**
     * Returns company
     *
     * @return string
     */
    public function getCompany();

    /**
     * Returns country_id
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Returns customer_address_id
     *
     * @return int
     */
    public function getCustomerAddressId();

    /**
     * Returns customer_id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Returns email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Returns fax
     *
     * @return string
     */
    public function getFax();

    /**
     * Returns firstname
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Returns lastname
     *
     * @return string
     */
    public function getLastname();

    /**
     * Returns middlename
     *
     * @return string
     */
    public function getMiddlename();

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId();

    /**
     * Returns postcode
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Returns prefix
     *
     * @return string
     */
    public function getPrefix();

    /**
     * Returns quote_address_id
     *
     * @return int
     */
    public function getQuoteAddressId();

    /**
     * Returns region
     *
     * @return string
     */
    public function getRegion();

    /**
     * Returns region_id
     *
     * @return int
     */
    public function getRegionId();

    /**
     * Get street
     *
     * @return string[]|null
     */
    public function getStreet();

    /**
     * Returns suffix
     *
     * @return string
     */
    public function getSuffix();

    /**
     * Returns telephone
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Returns vat_id
     *
     * @return string
     */
    public function getVatId();

    /**
     * Returns vat_is_valid
     *
     * @return int
     */
    public function getVatIsValid();

    /**
     * Returns vat_request_date
     *
     * @return string
     */
    public function getVatRequestDate();

    /**
     * Returns vat_request_id
     *
     * @return string
     */
    public function getVatRequestId();

    /**
     * Returns vat_request_success
     *
     * @return int
     */
    public function getVatRequestSuccess();
}
