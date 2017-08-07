<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;

/**
 * Class \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\LocationDirectory
 *
 * @since 2.1.0
 */
class LocationDirectory
{
    /**
     * @var array
     * @since 2.1.0
     */
    protected $regions;

    /**
     * @var array
     * @since 2.1.0
     */
    protected $iso2Countries;

    /**
     * @var array
     * @since 2.1.0
     */
    protected $iso3Countries;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     * @since 2.1.0
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     * @since 2.1.0
     */
    protected $_regionCollectionFactory;

    /**
     * LocationDirectory constructor.
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
    ) {
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @param string $countryCode
     * @return null|string
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @param string $countryCode
     * @return bool
     * @since 2.1.0
     */
    public function hasCountryId($countryCode)
    {
        $this->loadCountries();
        return isset($this->iso2Countries[$countryCode]) || isset($this->iso3Countries[$countryCode]);
    }

    /**
     * @param string $countryId
     * @param string $regionCode
     * @return bool
     * @since 2.1.0
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
     * @since 2.1.0
     */
    protected function loadRegions()
    {
        if ($this->regions !== null) {
            return $this;
        }

        $this->regions = [];

        /** @var $collection \Magento\Directory\Model\ResourceModel\Region\Collection */
        $collection = $this->_regionCollectionFactory->create();
        foreach ($collection->getData() as $row) {
            $this->regions[$row['country_id']][$row['code']] = (int)$row['region_id'];
        }

        return $this;
    }

    /**
     * @param int $countryId
     * @param string $regionCode
     * @return string
     * @since 2.1.0
     */
    public function getRegionId($countryId, $regionCode)
    {
        $this->loadRegions();
        return $this->regions[$countryId][$regionCode];
    }
}
