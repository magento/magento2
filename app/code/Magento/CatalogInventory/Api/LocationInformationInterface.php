<?php

namespace Magento\CatalogInventory\Api;

/**
 * Location information interface
 *
 * Designed to make possible location based
 * connections between inventory and delivery
 *
 * @api
 */
interface LocationInformationInterface
{
    /**
     * Company name of the location
     *
     * @return string
     */
    public function getCompany();

    /**
     * Returns multi-line street address
     *
     * @return string[]
     */
    public function getStreet();

    /**
     * Returns city name
     *
     * @return string
     */
    public function getCity();

    /**
     * Returns postcode (zipcode)
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Returns region string representation
     *
     * @see \Magento\Directory\Api\Data\RegionInformationInterface::getName()
     *
     * Also can be used as text based input for region
     *
     * @return string|null
     */
    public function getRegion();

    /**
     * Returns region identifier in directory
     *
     * @see \Magento\Directory\Api\Data\RegionInformationInterface::getId()
     *
     * @return string|null
     */
    public function getRegionId();


    /**
     * Returns region string code
     *
     * @see \Magento\Directory\Api\Data\RegionInformationInterface::getCode()
     *
     * @return string|null
     */
    public function getRegionCode();

    /**
     * Returns country identifier
     *
     * @see \Magento\Directory\Api\Data\CountryInformationInterface::getId()
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Returns latitude of location
     *
     * Designed to make possible geo location search
     *
     * @return string|null
     */
    public function getLatitude();

    /**
     * Returns longitude of location
     *
     * Designed to make possible geo location search
     *
     * @return string|null
     */
    public function getLongitude();
}
