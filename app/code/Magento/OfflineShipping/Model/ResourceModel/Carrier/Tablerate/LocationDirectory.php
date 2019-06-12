<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;

/**
 * Location directory.
 */
class LocationDirectory
{
    /**
     * @var array
     */
    protected $regions;

    /**
     * @var array
     */
    private $regionsByCode;

    /**
     * @var array
     */
    protected $iso2Countries;

    /**
     * @var array
     */
    protected $iso3Countries;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * LocationDirectory constructor.
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
    ) {
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * Retrieve country id.
     *
     * @param string $countryCode
     * @return null|string
     */
    public function getCountryId($countryCode)
    {
        $this->loadCountries();
        $countryId = null;
        if (isset($this->iso2Countries[$countryCode])) {
            $countryId = $this->iso2Countries[$countryCode];
        } elseif (isset($this->iso3Countries[$countryCode])) {
            $countryId = $this->iso3Countries[$countryCode];
        }

        return $countryId;
    }

    /**
     * Load directory countries
     *
     * @return \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate
     */
    protected function loadCountries()
    {
        if ($this->iso2Countries !== null && $this->iso3Countries !== null) {
            return $this;
        }

        $this->iso2Countries = [];
        $this->iso3Countries = [];

        /** @var $collection \Magento\Directory\Model\ResourceModel\Country\Collection */
        $collection = $this->_countryCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->iso2Countries[$row['iso2_code']] = $row['country_id'];
            $this->iso3Countries[$row['iso3_code']] = $row['country_id'];
        }

        return $this;
    }

    /**
     * Check if there is country id with provided country code.
     *
     * @param string $countryCode
     * @return bool
     */
    public function hasCountryId($countryCode)
    {
        $this->loadCountries();
        return isset($this->iso2Countries[$countryCode]) || isset($this->iso3Countries[$countryCode]);
    }

    /**
     * Check if there is region id with provided region code and country id.
     *
     * @param string $countryId
     * @param string $regionCode
     * @return bool
     */
    public function hasRegionId($countryId, $regionCode)
    {
        $this->loadRegions();
        return isset($this->regions[$countryId][$regionCode]);
    }

    /**
     * Load directory regions
     *
     * @return \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate
     */
    protected function loadRegions()
    {
        if ($this->regions !== null && $this->regionsByCode !== null) {
            return $this;
        }

        $this->regions = [];
        $this->regionsByCode = [];

        /** @var $collection \Magento\Directory\Model\ResourceModel\Region\Collection */
        $collection = $this->_regionCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->regions[$row['country_id']][$row['code']] = (int)$row['region_id'];
            if (empty($this->regionsByCode[$row['country_id']][$row['code']])) {
                $this->regionsByCode[$row['country_id']][$row['code']] = [];
            }
            $this->regionsByCode[$row['country_id']][$row['code']][] = (int)$row['region_id'];
        }

        return $this;
    }

    /**
     * Retrieve region id.
     *
     * @param int $countryId
     * @param string $regionCode
     * @return string
     * @deprecated
     */
    public function getRegionId($countryId, $regionCode)
    {
        $this->loadRegions();
        return $this->regions[$countryId][$regionCode];
    }

    /**
     * Return region ids for country and region
     *
     * @param int $countryId
     * @param string $regionCode
     * @return array
     */
    public function getRegionIds($countryId, $regionCode)
    {
        $this->loadRegions();
        return $this->regionsByCode[$countryId][$regionCode];
    }
}
