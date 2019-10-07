<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model\ResourceModel\Country;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Country Resource Collection
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Locale model
     *
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Directory\Model\ResourceModel\CountryFactory
     */
    protected $_countryFactory;

    /**
     * Array utils object
     *
     * @var \Magento\Framework\Stdlib\ArrayUtils
     */
    protected $_arrayUtils;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $helperData;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var string[]
     * @since 100.1.0
     */
    protected $countriesWithNotRequiredStates;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Model\ResourceModel\CountryFactory $countryFactory
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\App\Helper\AbstractHelper $helperData
     * @param array $countriesWithNotRequiredStates
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @param \Magento\Store\Model\StoreManagerInterface|null $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\ResourceModel\CountryFactory $countryFactory,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\App\Helper\AbstractHelper $helperData,
        array $countriesWithNotRequiredStates = [],
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_scopeConfig = $scopeConfig;
        $this->_localeLists = $localeLists;
        $this->_localeResolver = $localeResolver;
        $this->_countryFactory = $countryFactory;
        $this->_arrayUtils = $arrayUtils;
        $this->helperData = $helperData;
        $this->countriesWithNotRequiredStates = $countriesWithNotRequiredStates;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        );
    }

    /**
     * Foreground countries
     *
     * @var array
     */
    protected $_foregroundCountries = [];

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Directory\Model\Country::class, \Magento\Directory\Model\ResourceModel\Country::class);
    }

    /**
     * Return Allowed Countries reader
     *
     * @deprecated 100.1.2
     * @return \Magento\Directory\Model\AllowedCountries
     */
    private function getAllowedCountriesReader()
    {
        if (!$this->allowedCountriesReader) {
            $this->allowedCountriesReader = ObjectManager::getInstance()->get(AllowedCountries::class);
        }

        return $this->allowedCountriesReader;
    }

    /**
     * Load allowed countries for current store
     *
     * @param null|int|string|\Magento\Store\Model\Store $store
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function loadByStore($store = null)
    {
        $allowedCountries = $this->getAllowedCountriesReader()
            ->getAllowedCountries(ScopeInterface::SCOPE_STORE, $store);

        if (!empty($allowedCountries)) {
            $this->addFieldToFilter("country_id", ['in' => $allowedCountries]);
        }

        return $this;
    }

    /**
     * Loads Item By Id
     *
     * @param string $countryId
     * @return \Magento\Directory\Model\ResourceModel\Country|null
     */
    public function getItemById($countryId)
    {
        foreach ($this->_items as $country) {
            if ($country->getCountryId() == $countryId) {
                return $country;
            }
        }
        return null;
    }

    /**
     * Add filter by country code to collection.
     *
     * $countryCode can be either array of country codes or string representing one country code.
     * $iso can be either array containing 'iso2', 'iso3' values or string with containing one of that values directly.
     * The collection will contain countries where at least one of country $iso fields matches $countryCode.
     *
     * @param string|string[] $countryCode
     * @param string|string[] $iso
     * @return $this
     */
    public function addCountryCodeFilter($countryCode, $iso = ['iso3', 'iso2'])
    {
        if (!empty($countryCode)) {
            if (is_array($countryCode)) {
                if (is_array($iso)) {
                    $whereOr = [];
                    foreach ($iso as $iso_curr) {
                        $whereOr[] .= $this->_getConditionSql("{$iso_curr}_code", ['in' => $countryCode]);
                    }
                    $this->_select->where('(' . implode(') OR (', $whereOr) . ')');
                } else {
                    $this->addFieldToFilter("{$iso}_code", ['in' => $countryCode]);
                }
            } else {
                if (is_array($iso)) {
                    $whereOr = [];
                    foreach ($iso as $iso_curr) {
                        $whereOr[] .= $this->_getConditionSql("{$iso_curr}_code", $countryCode);
                    }
                    $this->_select->where('(' . implode(') OR (', $whereOr) . ')');
                } else {
                    $this->addFieldToFilter("{$iso}_code", $countryCode);
                }
            }
        }
        return $this;
    }

    /**
     * Add filter by country code(s) to collection
     *
     * @param string|string[] $countryId
     * @return $this
     */
    public function addCountryIdFilter($countryId)
    {
        if (!empty($countryId)) {
            if (is_array($countryId)) {
                $this->addFieldToFilter("country_id", ['in' => $countryId]);
            } else {
                $this->addFieldToFilter("country_id", $countryId);
            }
        }
        return $this;
    }

    /**
     * Convert collection items to select options array
     *
     * @param string|boolean $emptyLabel
     * @return array
     */
    public function toOptionArray($emptyLabel = ' ')
    {
        $options = $this->_toOptionArray('country_id', 'name', ['title' => 'iso2_code']);
        $sort = $this->getSort($options);

        $this->_arrayUtils->ksortMultibyte($sort, $this->_localeResolver->getLocale());
        foreach (array_reverse($this->_foregroundCountries) as $foregroundCountry) {
            $name = array_search($foregroundCountry, $sort);
            if ($name) {
                unset($sort[$name]);
                $sort = [$name => $foregroundCountry] + $sort;
            }
        }
        $isRegionVisible = (bool)$this->helperData->isShowNonRequiredState();

        $options = [];
        foreach ($sort as $label => $value) {
            $option = ['value' => $value, 'label' => $label];
            if ($this->helperData->isRegionRequired($value)) {
                $option['is_region_required'] = true;
            } else {
                $option['is_region_visible'] = $isRegionVisible;
            }
            if ($this->helperData->isZipCodeOptional($value)) {
                $option['is_zipcode_optional'] = true;
            }
            $options[] = $option;
        }
        if ($emptyLabel !== false && count($options) > 1) {
            array_unshift($options, ['value' => '', 'label' => $emptyLabel]);
        }

        $this->addDefaultCountryToOptions($options);

        return $options;
    }

    /**
     * Adds default country to options
     *
     * @param array $options
     * @return void
     */
    private function addDefaultCountryToOptions(array &$options)
    {
        $defaultCountry = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            $defaultCountryConfig = $this->_scopeConfig->getValue(
                \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_WEBSITES,
                $website
            );
            $defaultCountry[$defaultCountryConfig][] = $website->getId();
        }

        foreach ($options as $key => $option) {
            if (isset($defaultCountry[$option['value']])) {
                $options[$key]['is_default'] = !empty($defaultCountry[$option['value']]);
            }
        }
    }

    /**
     * Set foreground countries array
     *
     * @param string|array $foregroundCountries
     * @return $this
     */
    public function setForegroundCountries($foregroundCountries)
    {
        if (empty($foregroundCountries)) {
            return $this;
        }
        $this->_foregroundCountries = (array)$foregroundCountries;
        return $this;
    }

    /**
     * Get list of countries with required states
     *
     * @return \Magento\Directory\Model\Country[]
     * @since 100.1.0
     */
    public function getCountriesWithRequiredStates()
    {
        $countries = [];
        foreach ($this->getItems() as $country) {
            /** @var \Magento\Directory\Model\Country $country  */
            if ($country->getRegionCollection()->getSize() > 0
                && !in_array($country->getId(), $this->countriesWithNotRequiredStates)
            ) {
                $countries[$country->getId()] = $country;
            }
        }
        return $countries;
    }

    /**
     * Get sort
     *
     * @param array $options
     * @return array
     */
    private function getSort(array $options): array
    {
        $sort = [];
        foreach ($options as $data) {
            $name = (string)$this->_localeLists->getCountryTranslation($data['value']);
            if (!empty($name)) {
                $sort[$name] = $data['value'];
            }
        }

        return $sort;
    }
}
