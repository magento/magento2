<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class CountryHandler
{
    const ALLOWED_COUNTRIES_PATH = 'general/country/allow';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Share
     */
    private $customerConfigShare;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Share $configShare
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerConfigShare = $configShare;
    }

    /**
     * Retrieve allowed countries list by filter and scope
     * @param null | int | array $filter
     * @param string $scope
     * @return array
     */
    public function getAllowedCountries(
        $filter = null,
        $scope = ScopeInterface::SCOPE_WEBSITE,
        $ignoreGlobalScope = false
    ) {
        if (empty($filter)) {
            $filter = $this->storeManager->getWebsite()->getId();
        }

        if ($this->customerConfigShare->isGlobalScope() && !$ignoreGlobalScope) {
            //Check if we have shared accounts - than merge all website allowed countries
            $filter = array_map(function(WebsiteInterface $website) {
                return $website->getId();
            }, $this->storeManager->getWebsites());
            $scope = ScopeInterface::SCOPE_WEBSITES;
        }

        switch ($scope) {
            case ScopeInterface::SCOPE_WEBSITES:
            case ScopeInterface::SCOPE_STORES:
                $allowedCountries = [];
                foreach ($filter as $singleFilter) {
                    $allowedCountries = array_merge(
                        $allowedCountries,
                        $this->getCountriesFromConfig($this->getSingleScope($scope), $singleFilter)
                    );
                }
                break;
            default:
                $allowedCountries = $this->getCountriesFromConfig($scope, $filter);
        }

        return $this->makeCountriesUnique($allowedCountries);
    }

    /**
     * @param array $allowedCountries
     * @return array
     */
    private function makeCountriesUnique(array $allowedCountries)
    {
        return array_combine($allowedCountries, $allowedCountries);
    }

    /**
     * @param $scope
     * @param $filter
     * @return array
     */
    private function getCountriesFromConfig($scope, $filter)
    {
        return explode(',',
            (string) $this->scopeConfig->getValue(
                self::ALLOWED_COUNTRIES_PATH,
                $scope,
                $filter
            )
        );
    }

    /**
     * @param $filter
     * @param string $scope
     * @return $this
     */
    public function loadByScope($filter, $scope = ScopeInterface::SCOPE_STORE, AbstractDb $collection)
    {
        $allowCountries = $this->getAllowedCountries($filter, $scope);

        if (!empty($allowCountries)) {
            $collection->addFieldToFilter("country_id", ['in' => $allowCountries]);
        }

        return $collection;
    }

    /**
     * @param $scope
     * @return string
     */
    private function getSingleScope($scope)
    {
        if ($scope == ScopeInterface::SCOPE_WEBSITES) {
            return ScopeInterface::SCOPE_WEBSITE;
        }

        if ($scope == ScopeInterface::SCOPE_STORES) {
            return ScopeInterface::SCOPE_STORE;
        }

        return $scope;
    }
}
