<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\CustomerImportExport\Model\Import;

use Magento\Customer\Model\Config\Share as CustomerShareConfig;
use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Return allowed countries for specified website for customer import
 */
class CountryWithWebsites
{
    /**
     * @var CountryCollectionFactory
     */
    private $countriesFactory;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var array
     */
    private $allowedCountries;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerShareConfig
     */
    private $shareConfig;

    /**
     * @param CountryCollectionFactory $countriesFactory
     * @param AllowedCountries $allowedCountriesReader
     * @param StoreManagerInterface $storeManager
     * @param CustomerShareConfig $shareConfig
     */
    public function __construct(
        CountryCollectionFactory $countriesFactory,
        AllowedCountries $allowedCountriesReader,
        StoreManagerInterface $storeManager,
        CustomerShareConfig $shareConfig
    ) {
        $this->countriesFactory = $countriesFactory;
        $this->allowedCountriesReader = $allowedCountriesReader;
        $this->storeManager = $storeManager;
        $this->shareConfig = $shareConfig;
    }

    /**
     * Get allowed countries for specified website
     *
     * @return array
     */
    public function getCountiesPerWebsite(): array
    {
        if (!$this->allowedCountries) {
            $websiteIds = [];
            $allowedCountries = [];

            foreach ($this->storeManager->getWebsites() as $website) {
                $countries = $this->allowedCountriesReader
                    ->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, $website->getId());
                $allowedCountries[] = $countries;

                foreach ($countries as $countryCode) {
                    $websiteIds[$countryCode][] = $website->getId();
                }
            }

            $this->allowedCountries = $this->createCountriesCollection()
                ->addFieldToFilter('country_id', ['in' => $allowedCountries])
                ->toOptionArray();

            foreach ($this->allowedCountries as &$option) {
                if (isset($websiteIds[$option['value']])) {
                    $option['website_ids'] = $websiteIds[$option['value']];
                }
            }
        }

        return $this->allowedCountries;
    }

    /**
     * Create Countries Collection with all countries
     *
     * @return CountryCollection
     */
    private function createCountriesCollection()
    {
        return $this->countriesFactory->create();
    }
}
