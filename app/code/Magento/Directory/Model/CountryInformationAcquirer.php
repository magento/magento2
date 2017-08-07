<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Currency information acquirer class
 */
class CountryInformationAcquirer implements \Magento\Directory\Api\CountryInformationAcquirerInterface
{
    /**
     * @var \Magento\Directory\Model\Data\CountryInformationFactory
     */
    protected $countryInformationFactory;

    /**
     * @var \Magento\Directory\Model\Data\RegionInformationFactory
     */
    protected $regionInformationFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Directory\Model\Data\CountryInformationFactory $countryInformationFactory
     * @param \Magento\Directory\Model\Data\RegionInformationFactory $regionInformationFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Directory\Model\Data\CountryInformationFactory $countryInformationFactory,
        \Magento\Directory\Model\Data\RegionInformationFactory $regionInformationFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->countryInformationFactory = $countryInformationFactory;
        $this->regionInformationFactory = $regionInformationFactory;
        $this->directoryHelper = $directoryHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountriesInfo()
    {
        $countriesInfo = [];

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $storeLocale = $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getCode()
        );

        $countries = $this->directoryHelper->getCountryCollection($store);
        $regions = $this->directoryHelper->getRegionData();
        foreach ($countries as $data) {
            $countryInfo = $this->setCountryInfo($data, $regions, $storeLocale);
            $countriesInfo[] = $countryInfo;
        }

        return $countriesInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryInfo($countryId)
    {
        $store = $this->storeManager->getStore();
        $storeLocale = $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getCode()
        );

        $countriesCollection = $this->directoryHelper->getCountryCollection($store)->load();
        $regions = $this->directoryHelper->getRegionData();
        $country = $countriesCollection->getItemById($countryId);

        if (!$country) {
            throw new NoSuchEntityException(
                __(
                    'Requested country is not available.'
                )
            );
        }
        $countryInfo = $this->setCountryInfo($country, $regions, $storeLocale);

        return $countryInfo;
    }

    /**
     * Creates and initializes the information for \Magento\Directory\Model\Data\CountryInformation
     *
     * @param \Magento\Directory\Model\ResourceModel\Country $country
     * @param array $regions
     * @param string $storeLocale
     * @return \Magento\Directory\Model\Data\CountryInformation
     */
    protected function setCountryInfo($country, $regions, $storeLocale)
    {
        $countryId = $country->getCountryId();
        $countryInfo = $this->countryInformationFactory->create();
        $countryInfo->setId($countryId);
        $countryInfo->setTwoLetterAbbreviation($country->getData('iso2_code'));
        $countryInfo->setThreeLetterAbbreviation($country->getData('iso3_code'));
        $countryInfo->setFullNameLocale($country->getName($storeLocale));
        $countryInfo->setFullNameEnglish($country->getName('en_US'));

        if (array_key_exists($countryId, $regions)) {
            $regionsInfo = [];
            foreach ($regions[$countryId] as $id => $regionData) {
                $regionInfo = $this->regionInformationFactory->create();
                $regionInfo->setId($id);
                $regionInfo->setCode($regionData['code']);
                $regionInfo->setName($regionData['name']);
                $regionsInfo[] = $regionInfo;
            }
            $countryInfo->setAvailableRegions($regionsInfo);
        }

        return $countryInfo;
    }
}
