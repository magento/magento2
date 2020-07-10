<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Store;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Model\ResourceModel\StoreWebsiteRelation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Api\Data\StoreInterface;

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $extendedConfigData;

    /**
     * @var StoreWebsiteRelation
     */
    private $storeWebsiteRelation;

    /**
     * @param StoreConfigManagerInterface $storeConfigManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreWebsiteRelation $storeWebsiteRelation
     * @param array $extendedConfigData
     */
    public function __construct(
        StoreConfigManagerInterface $storeConfigManager,
        ScopeConfigInterface $scopeConfig,
        StoreWebsiteRelation $storeWebsiteRelation,
        array $extendedConfigData = []
    ) {
        $this->storeConfigManager = $storeConfigManager;
        $this->scopeConfig = $scopeConfig;
        $this->extendedConfigData = $extendedConfigData;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
    }

    /**
     * Get store config data
     *
     * @param StoreInterface $store
     * @return array
     */
    public function getStoreConfigData(StoreInterface $store): array
    {
        $defaultStoreConfig = $this->storeConfigManager->getStoreConfigs([$store->getCode()]);
        return $this->prepareStoreConfigData(current($defaultStoreConfig), $store->getName());
    }

    /**
     * Get available website stores
     *
     * @param int $websiteId
     * @return array
     */
    public function getAvailableStoreConfig(int $websiteId): array
    {
        $websiteStores = $this->storeWebsiteRelation->getWebsiteStores($websiteId, true);
        $storeCodes = array_column($websiteStores, 'code');

        $storeConfigs = $this->storeConfigManager->getStoreConfigs($storeCodes);
        $storesConfigData = [];

        foreach ($storeConfigs as $storeConfig) {
            $key = array_search($storeConfig->getCode(), array_column($websiteStores, 'code'), true);
            $storesConfigData[] = $this->prepareStoreConfigData($storeConfig, $websiteStores[$key]['name']);
        }

        return $storesConfigData;
    }

    /**
     * Prepare store config data
     *
     * @param StoreConfigInterface $storeConfig
     * @param string $storeName
     * @return array
     */
    private function prepareStoreConfigData(StoreConfigInterface $storeConfig, string $storeName): array
    {
        return array_merge([
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
            'base_static_url' => $storeConfig->getBaseStaticUrl(),
            'base_media_url' => $storeConfig->getBaseMediaUrl(),
            'secure_base_url' => $storeConfig->getSecureBaseUrl(),
            'secure_base_link_url' => $storeConfig->getSecureBaseLinkUrl(),
            'secure_base_static_url' => $storeConfig->getSecureBaseStaticUrl(),
            'secure_base_media_url' => $storeConfig->getSecureBaseMediaUrl(),
            'store_name' => $storeName,
        ], $this->getExtendedConfigData((int)$storeConfig->getId()));
    }

    /**
     * Get extended config data
     *
     * @param int $storeId
     * @return array
     */
    private function getExtendedConfigData(int $storeId): array
    {
        $extendedConfigData = [];
        foreach ($this->extendedConfigData as $key => $path) {
            $extendedConfigData[$key] = $this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $extendedConfigData;
    }
}
