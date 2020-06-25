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
use Magento\Store\Model\ResourceModel\Store\Collection;
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
     * @var Collection
     */
    private $storeCollection;

    /**
     * @param StoreConfigManagerInterface $storeConfigManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreWebsiteRelation $storeWebsiteRelation
     * @param Collection $storeCollection
     * @param array $extendedConfigData
     */
    public function __construct(
        StoreConfigManagerInterface $storeConfigManager,
        ScopeConfigInterface $scopeConfig,
        StoreWebsiteRelation $storeWebsiteRelation,
        Collection $storeCollection,
        array $extendedConfigData = []
    ) {
        $this->storeConfigManager = $storeConfigManager;
        $this->scopeConfig = $scopeConfig;
        $this->extendedConfigData = $extendedConfigData;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->storeCollection = $storeCollection;
    }

    /**
     * Get store config data
     *
     * @param StoreInterface $store
     * @return array
     */
    public function getStoreConfigData(StoreInterface $store): array
    {
        $defaultStoreConfig = $this->storeConfigManager->getStoreConfig($store);
        return $this->prepareStoreConfigData($defaultStoreConfig);
    }

    /**
     * Get website available stores
     *
     * @param StoreInterface $store
     * @return array
     */
    public function getAvailableStoreConfig(StoreInterface $store): array
    {
        $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($store->getWebsiteId());
        $websiteStores = $this->storeCollection->addIdFilter($storeIds);
        $storesConfigData = [];

        foreach ($websiteStores as $websiteStore) {
            if ($websiteStore->getIsActive()) {
                $storesConfigData[] = $this->prepareStoreConfigData(
                    $this->storeConfigManager->getStoreConfig($websiteStore)
                );
            }
        }

        return $storesConfigData;
    }

    /**
     * Prepare store config data
     *
     * @param StoreConfigInterface $storeConfig
     * @return array
     */
    private function prepareStoreConfigData(StoreConfigInterface $storeConfig): array
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
            'secure_base_media_url' => $storeConfig->getSecureBaseMediaUrl()
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
