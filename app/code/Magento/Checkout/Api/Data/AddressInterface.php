<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Api\Data;

/**
 * @see \Magento\Checkout\Service\V1\Data\Cart\Address
 */
interface AddressInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get region
     *
     * @TODO RegionInterface must be in Directory module
     * @return \Magento\Customer\Api\Data\RegionInterface|null
     */
    public function getRegion();

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
