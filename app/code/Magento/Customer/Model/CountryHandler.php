<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Model\Config\Share;
use Magento\Directory\Model\CountryHandlerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CountryHandler.
 * @package Magento\Customer\Model
 */
class CountryHandler implements CountryHandlerInterface
{
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

    /**
     * CountryHandler constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Config\Share $configShare
     */
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
     * @inheritdoc
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
            $filter = array_map(function (WebsiteInterface $website) {
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
     * @inheritdoc
     */
    public function loadByScope(AbstractDb $collection, $filter, $scope = ScopeInterface::SCOPE_STORE)
    {
        $allowCountries = $this->getAllowedCountries($filter, $scope);

        if (!empty($allowCountries)) {
            $collection->addFieldToFilter("country_id", ['in' => $allowCountries]);
        }

        return $collection;
    }

    /**
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
