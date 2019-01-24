<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Store;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * StoreConfig field data provider, used for GraphQL request processing.
 */
class StoreConfigDataProvider
{
    /**
     * @var StoreConfigManagerInterface
     */
    private $storeConfigManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $extendedConfigData;

    /**
     * @param StoreConfigManagerInterface $storeConfigManager
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $extendedConfigData
     */
    public function __construct(
        StoreConfigManagerInterface $storeConfigManager,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $extendedConfigData = []
    ) {
        $this->storeConfigManager = $storeConfigManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->extendedConfigData = $extendedConfigData;
    }

    /**
     * Get store config data
     *
     * @return array
     */
    public function getStoreConfigData(): array
    {
        $storeConfigData = array_merge(
            $this->getBaseConfigData(),
            $this->getExtendedConfigData()
        );
        return $storeConfigData;
    }

    /**
     * Get base config data
     *
     * @return array
     */
    private function getBaseConfigData() : array
    {
        $store = $this->storeManager->getStore();
        $storeConfig = current($this->storeConfigManager->getStoreConfigs([$store->getCode()]));

        $storeConfigData = [
            'id' => $storeConfig->getId(),
            'code' => $storeConfig->getCode(),
            'website_id' => $storeConfig->getWebsiteId(),
            'locale' => $storeConfig->getLocale(),
            'base_currency_code' => $storeConfig->getBaseCurrencyCode(),
            'default_display_currency_code' => $storeConfig->getDefaultDisplayCurrencyCode(),
            'timezone' => $storeConfig->getTimezone(),
            'weight_unit' => $storeConfig->getWeightUnit(),
            'base_url' => $storeConfig->getBaseUrl(),
            'base_link_url' => $storeConfig->getBaseLinkUrl(),
            'base_static_url' => $storeConfig->getSecureBaseStaticUrl(),
            'base_media_url' => $storeConfig->getBaseMediaUrl(),
            'secure_base_url' => $storeConfig->getSecureBaseUrl(),
            'secure_base_link_url' => $storeConfig->getSecureBaseLinkUrl(),
            'secure_base_static_url' => $storeConfig->getSecureBaseStaticUrl(),
            'secure_base_media_url' => $storeConfig->getSecureBaseMediaUrl()
        ];
        return $storeConfigData;
    }

    /**
     * Get extended config data
     *
     * @return array
     */
    private function getExtendedConfigData()
    {
        $store = $this->storeManager->getStore();
        $extendedConfigData = [];
        foreach ($this->extendedConfigData as $key => $path) {
            $extendedConfigData[$key] = $this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
        }
        return $extendedConfigData;
    }
}
