<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model;

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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Directory\Model\Data\CountryInformationFactory $countryInformationFactory
     *
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Directory\Model\Data\CountryInformationFactory $countryInformationFactory,
        \Magento\Directory\Model\Data\RegionInformationFactory $regionInformationFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->countryInformationFactory = $countryInformationFactory;
        $this->regionInformationFactory = $regionInformationFactory;
        $this->directoryHelper = $directoryHelper;
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

        $countries = $this->directoryHelper->getCountryCollection($store);
        $regions = $this->directoryHelper->getRegionData();
        foreach ($countries as $code => $data) {
            $countryInfo = $this->countryInformationFactory->create();
            $countryInfo->setId($code);
            $countryInfo->setTwoLetterAbbreviation($data->getData('iso2_code'));
            $countryInfo->setThreeLetterAbbreviation($data->getData('iso3_code'));
            $countryInfo->setFullNameLocale($data->getName());
            $countryInfo->setFullNameEnglish($data->getName('en_US'));
            if (array_key_exists($code, $regions)) {
                $regionsInfo = [];
                foreach ($regions as $key => $regionsData) {
                    if ($key == 'config') {
                        continue;
                    } else if ($key == $code) {
                        foreach ($regionsData as $key => $regionData) {
                            $regionInfo = $this->regionInformationFactory->create();
                            $regionInfo->setId($key);
                            $regionInfo->setCode($regionData['code']);
                            $regionInfo->setName($regionData['name']);
                            $regionsInfo[] = $regionInfo;
                        }
                        break;
                    }
                }
                $countryInfo->setAvailableRegions($regionsInfo);
            }
            $countriesInfo[] = $countryInfo;
        }

        return $countriesInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryInfo($countryId)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        $countries = $this->directoryHelper->getCountryCollection($store);
        $regions = $this->directoryHelper->getRegionData();

        $country = $countries->getItemById($countryId);
        $countryInfo = $this->countryInformationFactory->create();
        $countryInfo->setId($country->getCountryId());
        $countryInfo->setTwoLetterAbbreviation($country->getData('iso2_code'));
        $countryInfo->setThreeLetterAbbreviation($country->getData('iso3_code'));
        $countryInfo->setFullNameLocale($country->getName());
        $countryInfo->setFullNameEnglish($country->getName('en_US'));

        if (array_key_exists($country->getCountryId(), $regions)) {
            $regionsInfo = [];
            foreach ($regions as $key => $regionsData) {
                if ($key == 'config') {
                    continue;
                } else if ($key == $country->getCountryId()) {
                    foreach ($regionsData as $key => $regionData) {
                        $regionInfo = $this->regionInformationFactory->create();
                        $regionInfo->setId($key);
                        $regionInfo->setCode($regionData['code']);
                        $regionInfo->setName($regionData['name']);
                        $regionsInfo[] = $regionInfo;
                    }
                    break;
                }
            }
            $countryInfo->setAvailableRegions($regionsInfo);
        }

        return $countryInfo;
    }
}
