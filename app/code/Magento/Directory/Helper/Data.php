<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Helper;

use Magento\Directory\Model\AllowedCountries;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data as JsonData;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Directory data helper
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    private const STORE_ID = 'store_id';

    /**
     * Config value that lists ISO2 country codes which have optional Zip/Postal pre-configured
     */
    const OPTIONAL_ZIP_COUNTRIES_CONFIG_PATH = 'general/country/optional_zip_countries';

    /*
     * Path to config value, which lists countries, for which state is required.
     */
    const XML_PATH_STATES_REQUIRED = 'general/region/state_required';

    /*
     * Path to config value, which detects whether or not display the state for the country, if it is not required
     */
    const XML_PATH_DISPLAY_ALL_STATES = 'general/region/display_all';

    /**#@+
     * Path to config value, which is default country
     */
    const XML_PATH_DEFAULT_COUNTRY = 'general/country/default';
    const XML_PATH_DEFAULT_LOCALE = 'general/locale/code';
    const XML_PATH_DEFAULT_TIMEZONE = 'general/locale/timezone';
    /**#@-*/

    /**
     * Path to config value that contains codes of the most used countries.
     * Such countries can be shown on the top of the country list.
     */
    const XML_PATH_TOP_COUNTRIES = 'general/country/destinations';

    /**
     * Path to config value that contains weight unit
     */
    const XML_PATH_WEIGHT_UNIT = 'general/locale/weight_unit';

    /**
     * Country collection
     *
     * @var Collection
     */
    protected $_countryCollection;

    /**
     * Region collection
     *
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $_regionCollection;

    /**
     * Json representation of regions data
     *
     * @var string
     */
    protected $_regionJson;

    /**
     * Currency cache
     *
     * @var array
     */
    protected $_currencyCache = [];

    /**
     * ISO2 country codes which have optional Zip/Postal pre-configured
     *
     * @var array
     */
    protected $_optZipCountries = null;

    /**
     * @var Config
     */
    protected $_configCacheType;

    /**
     * @var CollectionFactory
     */
    protected $_regCollectionFactory;

    /**
     * @var JsonData
     */
    protected $jsonHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Config $configCacheType
     * @param Collection $countryCollection
     * @param CollectionFactory $regCollectionFactory
     * @param JsonData $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        Context $context,
        Config $configCacheType,
        Collection $countryCollection,
        CollectionFactory $regCollectionFactory,
        JsonData $jsonHelper,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory
    ) {
        parent::__construct($context);
        $this->_configCacheType = $configCacheType;
        $this->_countryCollection = $countryCollection;
        $this->_regCollectionFactory = $regCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
    }

    /**
     * Retrieve region collection
     *
     * @return \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    public function getRegionCollection()
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = $this->_regCollectionFactory->create();
            // phpstan:ignore
            $this->_regionCollection->addCountryFilter($this->getAddress()->getCountryId())->load();
        }
        return $this->_regionCollection;
    }

    /**
     * Retrieve country collection
     *
     * @param null|int|string|\Magento\Store\Model\Store $store
     * @return Collection
     */
    public function getCountryCollection($store = null)
    {
        if (!$this->_countryCollection->isLoaded()) {
            $this->_countryCollection->loadByStore($store);
        }
        return $this->_countryCollection;
    }

    /**
     * Retrieve regions data json
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegionJson()
    {
        \Magento\Framework\Profiler::start('TEST: ' . __METHOD__, ['group' => 'TEST', 'method' => __METHOD__]);
        if (!$this->_regionJson) {
            $scope = $this->getCurrentScope();
            $scopeKey = $scope['value'] ? '_' . implode('_', $scope) : null;
            $cacheKey = 'DIRECTORY_REGIONS_JSON_STORE' . $scopeKey;
            $json = $this->_configCacheType->load($cacheKey);
            if (empty($json)) {
                $regions = $this->getRegionData();
                $json = $this->jsonHelper->jsonEncode($regions);
                if ($json === false) {
                    $json = 'false';
                }
                $this->_configCacheType->save($json, $cacheKey);
            }
            $this->_regionJson = $json;
        }

        \Magento\Framework\Profiler::stop('TEST: ' . __METHOD__);
        return $this->_regionJson;
    }

    /**
     * Convert currency
     *
     * @param float $amount
     * @param string $from
     * @param string $to
     *
     * @return float
     * @SuppressWarnings(PHPMD.ShortVariable)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function currencyConvert($amount, $from, $to = null)
    {
        if (empty($this->_currencyCache[$from])) {
            $this->_currencyCache[$from] = $this->_currencyFactory->create()->load($from);
        }
        if ($to === null) {
            $to = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        }
        $converted = $this->_currencyCache[$from]->convert($amount, $to);
        return $converted;
    }

    /**
     * Return ISO2 country codes, which have optional Zip/Postal pre-configured
     *
     * @param bool $asJson
     * @return array|string
     */
    public function getCountriesWithOptionalZip($asJson = false)
    {
        if (null === $this->_optZipCountries) {
            $value = trim(
                $this->scopeConfig->getValue(
                    self::OPTIONAL_ZIP_COUNTRIES_CONFIG_PATH,
                    ScopeInterface::SCOPE_STORE
                )
            );
            $this->_optZipCountries = preg_split('/\,/', $value, 0, PREG_SPLIT_NO_EMPTY);
        }
        if ($asJson) {
            return $this->jsonHelper->jsonEncode($this->_optZipCountries);
        }
        return $this->_optZipCountries;
    }

    /**
     * Check whether zip code is optional for specified country code
     *
     * @param string $countryCode
     * @return boolean
     */
    public function isZipCodeOptional($countryCode)
    {
        $this->getCountriesWithOptionalZip();
        return in_array($countryCode, $this->_optZipCountries);
    }

    /**
     * Returns the list of countries, for which region is required
     *
     * @param boolean $asJson
     * @return array|string
     */
    public function getCountriesWithStatesRequired($asJson = false)
    {
        $value = trim(
            $this->scopeConfig->getValue(
                self::XML_PATH_STATES_REQUIRED,
                ScopeInterface::SCOPE_STORE
            )
        );
        $countryList = preg_split('/\,/', $value, 0, PREG_SPLIT_NO_EMPTY);
        if ($asJson) {
            return $this->jsonHelper->jsonEncode($countryList);
        }
        return $countryList;
    }

    /**
     * Return, whether non-required state should be shown
     *
     * @return bool
     */
    public function isShowNonRequiredState()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_ALL_STATES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns flag, which indicates whether region is required for specified country
     *
     * @param string $countryId
     * @return bool
     */
    public function isRegionRequired($countryId)
    {
        $countyList = $this->getCountriesWithStatesRequired();
        if (!is_array($countyList)) {
            return false;
        }
        return in_array($countryId, $countyList);
    }

    /**
     * Retrieve application base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->scopeConfig->getValue(
            Currency::XML_PATH_CURRENCY_BASE,
            'default'
        );
    }

    /**
     * Return default country code
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return string
     */
    public function getDefaultCountry($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve regions data
     *
     * @return array
     */
    public function getRegionData()
    {
        $scope = $this->getCurrentScope();
        $allowedCountries = $this->scopeConfig->getValue(
            AllowedCountries::ALLOWED_COUNTRIES_PATH,
            $scope['type'],
            $scope['value']
        );
        $countryIds = explode(',', $allowedCountries);
        $collection = $this->_regCollectionFactory->create();
        $collection->addCountryFilter($countryIds)->load();
        $regions = [
            'config' => [
                'show_all_regions' => $this->isShowNonRequiredState(),
                'regions_required' => $this->getCountriesWithStatesRequired(),
            ],
        ];
        foreach ($collection as $region) {
            /** @var $region \Magento\Directory\Model\Region */
            if (!$region->getRegionId()) {
                continue;
            }
            $regions[$region->getCountryId()][$region->getRegionId()] = [
                'code' => $region->getCode(),
                'name' => (string)__($region->getName()),
            ];
        }
        return $regions;
    }

    /**
     * Retrieve list of codes of the most used countries
     *
     * @return array
     */
    public function getTopCountryCodes()
    {
        $configValue = (string)$this->scopeConfig->getValue(
            self::XML_PATH_TOP_COUNTRIES,
            ScopeInterface::SCOPE_STORE
        );
        return !empty($configValue) ? explode(',', $configValue) : [];
    }

    /**
     * Retrieve weight unit
     *
     * @return string
     */
    public function getWeightUnit()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WEIGHT_UNIT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get current scope from request
     *
     * @return array
     */
    private function getCurrentScope(): array
    {
        $scope = [
            'type' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            'value' => null,
        ];
        $request = $this->_getRequest();
        if ($request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $scope = [
                'type' => ScopeInterface::SCOPE_WEBSITE,
                'value' => $request->getParam(ScopeInterface::SCOPE_WEBSITE),
            ];
        } elseif ($request->getParam(ScopeInterface::SCOPE_STORE)) {
            $scope = [
                'type' => ScopeInterface::SCOPE_STORE,
                'value' => $request->getParam(ScopeInterface::SCOPE_STORE),
            ];
        } elseif ($request->getParam(self::STORE_ID)) {
            $scope = [
                'type' => ScopeInterface::SCOPE_STORE,
                'value' => $request->getParam(self::STORE_ID),
            ];
        }

        return $scope;
    }
}
