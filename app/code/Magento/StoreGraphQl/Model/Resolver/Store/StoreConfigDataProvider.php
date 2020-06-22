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
use Magento\Store\Api\StoreRepositoryInterface;
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
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param StoreConfigManagerInterface $storeConfigManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreRepositoryInterface $storeRepository
     * @param array $extendedConfigData
     */
    public function __construct(
        StoreConfigManagerInterface $storeConfigManager,
        ScopeConfigInterface $scopeConfig,
        StoreRepositoryInterface $storeRepository,
        array $extendedConfigData = []
    ) {
        $this->storeConfigManager = $storeConfigManager;
        $this->scopeConfig = $scopeConfig;
        $this->extendedConfigData = $extendedConfigData;
        $this->storeRepository = $storeRepository;
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
        $defaultStoreConfigData = $this->prepareStoreConfigData($defaultStoreConfig);

        return $this->addStores($defaultStoreConfigData);
    }

    /**
     * Add all stores
     *
     * @param array $defaultStoreConfigData
     * @return mixed
     */
    private function addStores($defaultStoreConfigData)
    {
        $stores = $this->storeRepository->getList();

        foreach ($stores as $store) {
            $storeConfig = $this->storeConfigManager->getStoreConfig($store);
            $defaultStoreConfigData['stores'][] = $this->prepareStoreConfigData($storeConfig);
        }

        return $defaultStoreConfigData;
    }

    /**
     * Prepare store config data
     *
     * @param StoreConfigInterface $storeConfig
     * @return array
     */
    private function prepareStoreConfigData($storeConfig): array
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
