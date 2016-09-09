<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CountryHandler.
 */
class AllowedCountries
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
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve all allowed countries for scope or scopes
     * @param string | null $filter
     * @param string $scope
     * @return array
     */
    public function getAllowedCountries(
        $filter = null,
        $scope = ScopeInterface::SCOPE_WEBSITE
    ) {
        if (empty($filter)) {
            $filter = $this->storeManager->getWebsite()->getId();
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

        return $this->getUniqueCountries($allowedCountries);
    }

    /**
     * Return Unique Countries by merging them by keys.
     * @param array $allowedCountries
     * @return array
     */
    private function getUniqueCountries(array $allowedCountries)
    {
        return array_combine($allowedCountries, $allowedCountries);
    }

    /**
     * Takes countries from Countries Config data
     * @param string $scope
     * @param int $filter
     * @return array
     */
    private function getCountriesFromConfig($scope, $filter)
    {
        return explode(
            ',',
            (string) $this->scopeConfig->getValue(
                self::ALLOWED_COUNTRIES_PATH,
                $scope,
                $filter
            )
        );
    }

    /**
     * Return Single Scope
     * @param string $scope
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
