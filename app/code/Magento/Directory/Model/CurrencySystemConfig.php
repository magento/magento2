<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide system config values for allowed, base and default currencies.
 */
class CurrencySystemConfig
{
    /**
     * @var System
     */
    private $systemConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $path;

    /**
     * Currency constructor.
     *
     * @param System $systemConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        System $systemConfig,
        StoreManagerInterface $storeManager,
        ResourceConnection $resources
    ) {
        $this->systemConfig = $systemConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve config currency data by config path.
     *
     * @param string $path
     * @return array
     */
    public function getConfigCurrencies(string $path)
    {
        $this->path = $path;
        $result = array_merge(
            $this->getDefaultConfigCurrencies(),
            $this->getWebsiteConfigCurrencies(),
            $this->getStoreConfigCurrencies()
        );
        sort($result);

        return array_unique($result);
    }

    /**
     * Get system config values as array for default scope.
     *
     * @return array
     */
    private function getDefaultConfigCurrencies()
    {
        return $this->getConfig($this->path, 'default');
    }

    /**
     * Get system config values as array for website scope.
     *
     * @return array
     */
    private function getWebsiteConfigCurrencies()
    {
        $websiteResult = [[]];
        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteResult[] = $this->getConfig($this->path, 'websites', $website->getId());
        }
        $websiteResult = array_merge(...$websiteResult);

        return $websiteResult;
    }

    /**
     * Get system config values as array for store scope.
     *
     * @return array
     */
    private function getStoreConfigCurrencies()
    {
        $storeResult = [[]];
        foreach ($this->storeManager->getStores() as $store) {
            $storeResult[] = $this->getConfig($this->path, 'stores', $store->getId());
        }
        $storeResult = array_merge(...$storeResult);

        return $storeResult;
    }

    /**
     * Get system config values as array for specified scope.
     *
     * @param string $scope
     * @param string $scopeId
     * @param string $path
     * @return array
     */
    private function getConfig(string $path, string $scope, string $scopeId = null)
    {
        $configPath = $scopeId ? sprintf('%s/%s/%s', $scope, $scopeId, $path) : sprintf('%s/%s', $scope, $path);

        return explode(',', $this->systemConfig->get($configPath));
    }
}
