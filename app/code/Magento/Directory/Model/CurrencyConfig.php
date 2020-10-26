<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide config values for allowed, base and default currencies.
 */
class CurrencyConfig
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CurrencyConfig constructor.
     *
     * @param State $appState
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        State $appState,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager
    ) {
        $this->appState = $appState;
        $this->config = $config;
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
        $result = in_array($this->appState->getAreaCode(), [Area::AREA_ADMINHTML, Area::AREA_CRONTAB])
            ? $this->getConfigForAllStores($path)
            : $this->getConfigForCurrentStore($path);
        sort($result);

        return array_unique($result);
    }

    /**
     * Get allowed, base and default currency codes for all stores.
     *
     * @param string $path
     * @return array
     */
    private function getConfigForAllStores(string $path)
    {
        $storesResult = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storesResult[] = explode(
                ',',
                $this->config->getValue($path, ScopeInterface::SCOPE_STORE, $store->getCode())
            );
        }

        return array_merge([], ...$storesResult);
    }

    /**
     * Get allowed, base and default currency codes for current store.
     *
     * @param string $path
     * @return mixed
     */
    private function getConfigForCurrentStore(string $path)
    {
        $store = $this->storeManager->getStore();

        return explode(',', $this->config->getValue($path, ScopeInterface::SCOPE_STORE, $store->getCode()));
    }
}
