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
        $defaultWebsiteStore = $this->storeWebsiteRelation->getWebsiteStores(
            (int) $store->getWebsiteId(),
            true,
            (int) $store->getStoreGroupId(),
            (int) $store->getStoreId()
        );
        if (empty($defaultWebsiteStore)) {
            return [];
        }

        $storeConfigs = $this->storeConfigManager->getStoreConfigs([$store->getCode()]);

        return $this->prepareStoreConfigData(current($storeConfigs), current($defaultWebsiteStore));
    }

    /**
     * Get available website stores
     *
     * @param int $websiteId
     * @param int|null $storeGroupId
     * @return array
     */
    public function getAvailableStoreConfig(int $websiteId, ?int $storeGroupId = null): array
    {
        $websiteStores = $this->storeWebsiteRelation->getWebsiteStores($websiteId, true, $storeGroupId);
        $storeCodes = array_column($websiteStores, 'code');

        $storeConfigs = $this->storeConfigManager->getStoreConfigs($storeCodes);
        $storesConfigData = [];

        foreach ($storeConfigs as $storeConfig) {
            $key = array_search($storeConfig->getCode(), array_column($websiteStores, 'code'), true);
            $storesConfigData[] = $this->prepareStoreConfigData($storeConfig, $websiteStores[$key]);
        }

        return $storesConfigData;
    }

    /**
     * Prepare store config data
     *
     * @param StoreConfigInterface $storeConfig
     * @param array $storeData
     * @return array
     */
    private function prepareStoreConfigData(StoreConfigInterface $storeConfig, array $storeData): array
    {
        return array_merge([
            'id' => $storeConfig->getId(),
            'code' => $storeConfig->getCode(),
            'store_code' => $storeConfig->getCode(),
            'store_name' => $storeData['name'] ?? null,
            'store_sort_order' => $storeData['sort_order'] ?? null,
            'is_default_store' => $storeData['default_store_id'] == $storeConfig->getId() ?? null,
            'store_group_code' => $storeData['store_group_code'] ?? null,
            'store_group_name' => $storeData['store_group_name'] ?? null,
            'is_default_store_group' => $storeData['default_group_id'] == $storeData['group_id'] ?? null,
            'store_group_default_store_code' => $storeData['store_group_default_store_code'] ?? null,
            'website_id' => $storeConfig->getWebsiteId(),
            'website_code' => $storeData['website_code'] ?? null,
            'website_name' => $storeData['website_name'] ?? null,
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
