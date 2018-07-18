<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Store;

use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;

/**
 * StoreConfig field data provider, used for GraphQL request processing.
 */
class StoreConfigsDataProvider
{
    /**
     * @var StoreConfigManagerInterface
     */
    private $storeConfigManager;

    /**
     * @param StoreConfigManagerInterface $storeConfigManager
     */
    public function __construct(
        StoreConfigManagerInterface $storeConfigManager
    ) {
        $this->storeConfigManager = $storeConfigManager;
    }

    /**
     * Get store configs by store codes
     *
     * @param array $storeCodes
     * @return array
     */
    public function getStoreConfigsByStoreCodes(array $storeCodes = null) : array
    {
        $storeConfigs = $this->storeConfigManager->getStoreConfigs($storeCodes);

        return [
            'items' => $this->hidrateStoreConfigs($storeConfigs)
        ];
    }

    /**
     * Transform StoreConfig objects to in array format
     *
     * @param StoreConfigInterface[] $storeConfigs
     * @return array
     */
    private function hidrateStoreConfigs(array $storeConfigs) : array
    {
        $storeConfigsData = [];
        /** @var StoreConfigInterface $storeConfig */
        foreach ($storeConfigs as $storeConfig) {
            $storeConfigsData[] = [
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
        }

        return $storeConfigsData;
    }
}
